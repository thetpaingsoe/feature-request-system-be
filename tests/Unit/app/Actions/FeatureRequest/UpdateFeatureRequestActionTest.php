<?php

namespace Tests\Unit\Actions\FeatureRequest;

use App\Actions\FeatureRequest\UpdateFeatureRequestAction;
use App\Models\FeatureRequest;
use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Throwable;

class UpdateFeatureRequestActionTest extends TestCase
{
    protected $featureRequestServiceMock;

    protected $updateFeatureRequestAction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for FeatureRequestService
        $this->featureRequestServiceMock = Mockery::mock(FeatureRequestService::class);

        // Instantiate the action with the mocked service
        $this->updateFeatureRequestAction = new UpdateFeatureRequestAction(
            $this->featureRequestServiceMock
        );

        // Mock DB and Log facades
        DB::shouldReceive('beginTransaction')->byDefault();
        DB::shouldReceive('commit')->byDefault();
        DB::shouldReceive('rollBack')->byDefault();
        Log::shouldReceive('error')->byDefault(); // Suppress actual logging in tests
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_updates_a_feature_request_successfully()
    {
        // Arrange
        $featureRequestId = 1;
        $newStatus = 'approved';
        $newNote = 'Reviewed and approved by admin.';

        // Mock the incoming request's validated data
        // If you use a FormRequest (e.g., FeatureRequestUpdateRequest), mock it like this:
        $mockRequest = Mockery::mock(Request::class); // Or your specific FormRequest class
        $mockRequest->shouldReceive('validated')
            ->once()
            ->andReturn(['status' => $newStatus, 'note' => $newNote]);

        // Create a mock FeatureRequest model that the service would return from 'get'
        $initialFeatureRequest = new FeatureRequest([
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'email' => 'initial@example.com',
            'status' => 'pending',
            'note' => null,
        ]);
        $initialFeatureRequest->forceFill(['id' => $featureRequestId]);

        // Create mock FeatureRequest models that the service would return after updates
        // Note: In a real scenario, the same object would be mutated and returned.
        // For mocking, we can return new instances or the same instance.
        $updatedFeatureRequestAfterStatus = clone $initialFeatureRequest;
        $updatedFeatureRequestAfterStatus->status = $newStatus;

        $finalUpdatedFeatureRequest = clone $updatedFeatureRequestAfterStatus;
        $finalUpdatedFeatureRequest->note = $newNote;

        // Expect calls on the mocked service
        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->with($featureRequestId)
            ->andReturn($initialFeatureRequest); // Returns the initial request

        $this->featureRequestServiceMock
            ->shouldReceive('updateStatus')
            ->once()
            ->with(
                Mockery::on(function ($arg) use ($initialFeatureRequest) {
                    return $arg->id === $initialFeatureRequest->id;
                }),
                $newStatus
            )
            ->andReturn($updatedFeatureRequestAfterStatus); // Returns the request after status update

        $this->featureRequestServiceMock
            ->shouldReceive('updateNote')
            ->once()
            ->with(
                Mockery::on(function ($arg) use ($updatedFeatureRequestAfterStatus, $newStatus) {
                    // Ensure the updateNote receives the model after status update
                    return $arg->id === $updatedFeatureRequestAfterStatus->id && $arg->status === $newStatus;
                }),
                $newNote
            )
            ->andReturn($finalUpdatedFeatureRequest); // Returns the request after note update

        // Expect DB transaction methods to be called
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldNotReceive('rollBack'); // Should not rollback on success
        Log::shouldNotReceive('error'); // Should not log error on success

        // Act
        $result = $this->updateFeatureRequestAction->handle($featureRequestId, $mockRequest);

        // Assert
        $this->assertEquals($finalUpdatedFeatureRequest, $result);
        $this->assertInstanceOf(FeatureRequest::class, $result);
        $this->assertEquals($featureRequestId, $result->id);
        $this->assertEquals($newStatus, $result->status);
        $this->assertEquals($newNote, $result->note);
    }

    #[Test]
    public function it_rolls_back_and_logs_on_exception_during_get()
    {
        // Arrange
        $featureRequestId = 1;
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('validated')->andReturn(['status' => 'approved', 'note' => 'Some note']);

        $exceptionMessage = 'Feature request not found.';
        $mockException = new \Exception($exceptionMessage);

        // Expect 'get' to throw an exception
        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->with($featureRequestId)
            ->andThrow($mockException);

        // 'updateStatus' and 'updateNote' should not be called
        $this->featureRequestServiceMock->shouldNotReceive('updateStatus');
        $this->featureRequestServiceMock->shouldNotReceive('updateNote');

        // Expect DB transaction methods to be called
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once(); // Should rollback on failure
        DB::shouldNotReceive('commit'); // Should not commit on failure

        // Expect an error to be logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'Error updating feature request') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        // Act & Assert: Expect the exception to be re-thrown
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->updateFeatureRequestAction->handle($featureRequestId, $mockRequest);
    }

    #[Test]
    public function it_rolls_back_and_logs_on_exception_during_update_status()
    {
        // Arrange
        $featureRequestId = 1;
        $newStatus = 'approved';
        $newNote = 'Some note';
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('validated')->andReturn(['status' => $newStatus, 'note' => $newNote]);

        $initialFeatureRequest = new FeatureRequest(['title' => 'Test', 'status' => 'pending']);
        $initialFeatureRequest->forceFill(['id' => $featureRequestId]);

        $exceptionMessage = 'Failed to update status.';
        $mockException = new \Exception($exceptionMessage);

        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($initialFeatureRequest);

        // Expect 'updateStatus' to throw an exception
        $this->featureRequestServiceMock
            ->shouldReceive('updateStatus')
            ->once()
            ->with(Mockery::on(function ($arg) use ($initialFeatureRequest) {
                return $arg->id === $initialFeatureRequest->id;
            }), $newStatus)
            ->andThrow($mockException);

        // 'updateNote' should not be called
        $this->featureRequestServiceMock->shouldNotReceive('updateNote');

        // Expect DB transaction methods to be called
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldNotReceive('commit');

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'Error updating feature request') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->updateFeatureRequestAction->handle($featureRequestId, $mockRequest);
    }

    #[Test]
    public function it_rolls_back_and_logs_on_exception_during_update_note()
    {
        // Arrange
        $featureRequestId = 1;
        $newStatus = 'approved';
        $newNote = 'Some note';
        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('validated')->andReturn(['status' => $newStatus, 'note' => $newNote]);

        $initialFeatureRequest = new FeatureRequest(['title' => 'Test', 'status' => 'pending']);
        $initialFeatureRequest->forceFill(['id' => $featureRequestId]);

        $updatedFeatureRequestAfterStatus = clone $initialFeatureRequest;
        $updatedFeatureRequestAfterStatus->status = $newStatus;

        $exceptionMessage = 'Failed to update note.';
        $mockException = new \Exception($exceptionMessage);

        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($initialFeatureRequest);

        $this->featureRequestServiceMock
            ->shouldReceive('updateStatus')
            ->once()
            ->andReturn($updatedFeatureRequestAfterStatus); // Simulate successful status update

        // Expect 'updateNote' to throw an exception
        $this->featureRequestServiceMock
            ->shouldReceive('updateNote')
            ->once()
            ->with(Mockery::on(function ($arg) use ($updatedFeatureRequestAfterStatus) {
                return $arg->id === $updatedFeatureRequestAfterStatus->id;
            }), $newNote)
            ->andThrow($mockException);

        // Expect DB transaction methods to be called
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldNotReceive('commit');

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'Error updating feature request') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->updateFeatureRequestAction->handle($featureRequestId, $mockRequest);
    }
}
