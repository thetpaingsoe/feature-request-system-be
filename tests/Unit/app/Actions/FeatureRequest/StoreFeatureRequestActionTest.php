<?php

namespace Tests\Unit\Actions\FeatureRequest;

use App\Actions\FeatureRequest\StoreFeatureRequestAction;
use App\DTOs\FeatureRequest\StoreFeatureRequestDto;
use App\Http\Requests\FeatureRequest\FeatureRequestStoreRequest;
use App\Models\FeatureRequest; // Import your DTO
use App\Services\FeatureRequest\FeatureRequestService; // Import your Form Request
use Illuminate\Support\Facades\Log; // Assuming your FeatureRequest model
use Mockery;
use PHPUnit\Framework\Attributes\Test; // For mocking dependencies
use Tests\TestCase; // Import the Test attribute
use Throwable;

class StoreFeatureRequestActionTest extends TestCase
{
    protected $featureRequestServiceMock;

    protected $storeFeatureRequestAction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for FeatureRequestService
        $this->featureRequestServiceMock = Mockery::mock(FeatureRequestService::class);

        // Instantiate the action with the mocked service
        $this->storeFeatureRequestAction = new StoreFeatureRequestAction(
            $this->featureRequestServiceMock
        );

        // Mock Log facade to prevent actual logging during tests
        Log::shouldReceive('error')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close(); // Close Mockery after each test
        parent::tearDown();
    }

    #[Test]
    public function it_stores_a_feature_request_successfully()
    {
        // Arrange
        $validatedData = [
            'title' => 'New Feature Idea',
            'description' => 'This is a detailed description of the new feature.',
            'email' => 'test@example.com',
        ];

        // Create a mock FeatureRequestStoreRequest
        // We only need to mock the validated() method
        $mockRequest = Mockery::mock(FeatureRequestStoreRequest::class);
        $mockRequest->shouldReceive('validated')->once()->andReturn($validatedData);

        // Create the expected DTO that should be passed to the service
        $expectedDto = StoreFeatureRequestDto::from($validatedData);

        // Create a mock FeatureRequest model that the service would return
        $createdFeatureRequest = new FeatureRequest($validatedData);
        $createdFeatureRequest->forceFill(['id' => 1]); // Simulate ID from DB

        // Expect the 'create' method on the mocked service to be called once
        // with an instance of StoreFeatureRequestDto that matches our expected data,
        // and return the mock FeatureRequest.
        $this->featureRequestServiceMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedDto) {
                // Assert that the argument is an instance of StoreFeatureRequestDto
                // and its properties match the expected DTO.
                return $arg instanceof StoreFeatureRequestDto &&
                       $arg->title === $expectedDto->title &&
                       $arg->description === $expectedDto->description &&
                       $arg->email === $expectedDto->email;
            }))
            ->andReturn($createdFeatureRequest);

        // Ensure no error is logged on success
        Log::shouldNotReceive('error');

        // Act
        $result = $this->storeFeatureRequestAction->handle($mockRequest);

        // Assert
        $this->assertEquals($createdFeatureRequest, $result);
        $this->assertInstanceOf(FeatureRequest::class, $result);
        $this->assertEquals(1, $result->id); // Verify ID from the mocked return
    }

    #[Test]
    public function it_logs_and_rethrows_exception_if_service_fails()
    {
        // Arrange
        $validatedData = [
            'title' => 'Failing Feature',
            'description' => 'This feature will cause an error.',
            'email' => 'fail@example.com',
        ];

        $mockRequest = Mockery::mock(FeatureRequestStoreRequest::class);
        $mockRequest->shouldReceive('validated')->once()->andReturn($validatedData);

        $exceptionMessage = 'Database error during creation.';
        $mockException = new \Exception($exceptionMessage);

        // Expect the 'create' method on the mocked service to throw an exception
        $this->featureRequestServiceMock
            ->shouldReceive('create')
            ->once()
            ->andThrow($mockException); // Simulate an error from the service

        // Expect an error to be logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'StoreFeatureRequestAction::handle') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        // Act & Assert: Expect the exception to be re-thrown by the action
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->storeFeatureRequestAction->handle($mockRequest);
    }
}
