<?php

namespace Tests\Unit\Actions\FeatureRequest;

use App\Actions\FeatureRequest\GetFeatureRequestAction;
use App\Models\FeatureRequest;
use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Support\Facades\Log; // Assuming your FeatureRequest model
use Mockery;
use PHPUnit\Framework\Attributes\Test; // For mocking dependencies
use Tests\TestCase; // Import the Test attribute
use Throwable;

class GetFeatureRequestActionTest extends TestCase
{
    protected $featureRequestServiceMock;

    protected $getFeatureRequestAction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for FeatureRequestService
        $this->featureRequestServiceMock = Mockery::mock(FeatureRequestService::class);

        // Instantiate the action with the mocked service
        $this->getFeatureRequestAction = new GetFeatureRequestAction(
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
    public function it_retrieves_a_feature_request_successfully()
    {
        // Arrange
        $featureRequestId = 5;
        $mockFeatureRequest = new FeatureRequest([
            'title' => 'Retrieve Test Feature',
            'description' => 'This is a feature for testing retrieval.',
            'email' => 'retrieve@example.com',
        ]);

        $mockFeatureRequest->forceFill(['id' => $featureRequestId]);

        // Expect the 'get' method on the mocked service to be called once
        // with the correct ID, and return the mock FeatureRequest.
        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->with($featureRequestId)
            ->andReturn($mockFeatureRequest);

        // Ensure no error is logged on success
        Log::shouldNotReceive('error');

        // Act
        $result = $this->getFeatureRequestAction->handle($featureRequestId);

        // Assert
        $this->assertEquals($mockFeatureRequest, $result);
        $this->assertInstanceOf(FeatureRequest::class, $result);

        $this->assertEquals($featureRequestId, $result->id);
    }

    #[Test]
    public function it_logs_and_rethrows_exception_if_service_fails()
    {
        // Arrange
        $featureRequestId = 10;
        $exceptionMessage = 'Feature request not found in service.';
        $mockException = new \Exception($exceptionMessage);

        // Expect the 'get' method on the mocked service to be called once
        // with the correct ID, and throw an exception.
        $this->featureRequestServiceMock
            ->shouldReceive('get')
            ->once()
            ->with($featureRequestId)
            ->andThrow($mockException); // Simulate an error from the service

        // Expect an error to be logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'Error getting feature request') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        // Act & Assert: Expect the exception to be re-thrown by the action
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->getFeatureRequestAction->handle($featureRequestId);
    }
}
