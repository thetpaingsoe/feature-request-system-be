<?php

namespace Tests\Unit\Actions\FeatureRequest;

use App\Actions\FeatureRequest\DeleteFeatureRequestAction;
use App\Models\FeatureRequest;
use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Throwable;

class DeleteFeatureRequestActionTest extends TestCase
{
    protected $featureRequestServiceMock;

    protected $deleteFeatureRequestAction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for FeatureRequestService
        $this->featureRequestServiceMock = Mockery::mock(FeatureRequestService::class);

        // Instantiate the action with the mocked service
        $this->deleteFeatureRequestAction = new DeleteFeatureRequestAction(
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
        Mockery::close(); // Close Mockery after each test
        parent::tearDown();
    }

    #[Test]
    public function it_deletes_a_feature_request_successfully()
    {
        // Arrange
        $featureRequestId = 1;
        $mockRequest = (object) ['id' => 1];
        $mockFeatureRequest = new FeatureRequest(['id' => $featureRequestId, 'title' => 'Test Feature']);

        // Expect calls on the mocked service
        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->with($featureRequestId)
            ->andReturn($mockFeatureRequest);

        $this->featureRequestServiceMock
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($arg) use ($mockFeatureRequest) {
                // Ensure the delete method receives the same FeatureRequest model instance
                return $arg->id === $mockFeatureRequest->id;
            }))
            ->andReturn(true); // Assuming delete returns boolean true on success

        // Expect DB transaction methods to be called
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldNotReceive('rollBack'); // Should not rollback on success
        Log::shouldNotReceive('error'); // Should not log error on success

        // Act
        $result = $this->deleteFeatureRequestAction->handle($featureRequestId, $mockRequest);

        // Assert
        $this->assertTrue($result); // Assuming delete returns true, or check the returned model if it returns that
    }

    #[Test]
    public function it_rolls_back_and_logs_on_exception()
    {
        // Arrange
        $featureRequestId = 1;
        $mockRequest = (object) ['id' => 1];
        $exceptionMessage = 'Something went wrong during deletion';
        $mockException = new \Exception($exceptionMessage);

        // Expect 'get' to throw an exception
        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->with($featureRequestId)
            ->andThrow($mockException); // Simulate an error during get

        // 'delete' should not be called if 'get' throws an exception
        $this->featureRequestServiceMock->shouldNotReceive('delete');

        // Expect DB transaction methods to be called
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once(); // Should rollback on failure
        DB::shouldNotReceive('commit'); // Should not commit on failure

        // Expect an error to be logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'Error delete feature request') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        // Act & Assert: Expect the exception to be re-thrown
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->deleteFeatureRequestAction->handle($featureRequestId, $mockRequest);
    }

    #[Test]
    public function it_rolls_back_and_logs_if_delete_fails()
    {
        // Arrange
        $featureRequestId = 1;
        $mockRequest = (object) ['id' => 1];
        $mockFeatureRequest = new FeatureRequest(['id' => $featureRequestId, 'title' => 'Test Feature']);
        $exceptionMessage = 'Failed to delete in service';
        $mockException = new \Exception($exceptionMessage);

        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->with($featureRequestId)
            ->andReturn($mockFeatureRequest);

        // Expect 'delete' to throw an exception
        $this->featureRequestServiceMock
            ->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($arg) use ($mockFeatureRequest) {
                return $arg->id === $mockFeatureRequest->id;
            }))
            ->andThrow($mockException); // Simulate an error during delete

        // Expect DB transaction methods to be called
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once(); // Should rollback on failure
        DB::shouldNotReceive('commit'); // Should not commit on failure

        // Expect an error to be logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'Error delete feature request') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        // Act & Assert: Expect the exception to be re-thrown
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->deleteFeatureRequestAction->handle($featureRequestId, $mockRequest);
    }
}
