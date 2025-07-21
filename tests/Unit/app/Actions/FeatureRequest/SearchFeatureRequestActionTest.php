<?php

namespace Tests\Unit\Actions\FeatureRequest;

use App\Actions\FeatureRequest\SearchFeatureRequestAction;
use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Throwable;

class SearchFeatureRequestActionTest extends TestCase
{
    protected $featureRequestServiceMock;

    protected $searchFeatureRequestAction;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for FeatureRequestService
        $this->featureRequestServiceMock = Mockery::mock(FeatureRequestService::class);

        // Instantiate the action with the mocked service
        $this->searchFeatureRequestAction = new SearchFeatureRequestAction(
            $this->featureRequestServiceMock
        );

        // Mock Log facade to prevent actual logging during tests
        Log::shouldReceive('error')->byDefault();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_searches_feature_requests_successfully_with_all_parameters()
    {
        // Arrange
        $mockRequestData = [
            'search' => 'test keyword',
            'status' => 'Pending',
            'date_start' => '2023-01-01',
            'date_end' => '2023-12-31',
            'sort_by' => 'submitted_at',
            'sort_direction' => 'desc',
            'page' => 2,
            'per_page' => 20,
            'other_param' => 'should_be_ignored', // Extra param to ensure 'only' works
        ];

        // Create a mock Request object
        $mockRequest = Request::create('/feature-requests', 'GET', $mockRequestData);

        $expectedFilters = [
            'search' => 'test keyword',
            'status' => 'Pending',
            'date_start' => '2023-01-01',
            'date_end' => '2023-12-31',
        ];
        $expectedSorting = [
            'sort_by' => 'submitted_at',
            'sort_direction' => 'desc',
        ];
        $expectedPage = 2;
        $expectedPerPage = 20;

        // Mock the paginated result from the service
        $mockFeatureRequests = collect([
            (object) ['id' => 1, 'title' => 'Feature A'],
            (object) ['id' => 2, 'title' => 'Feature B'],
        ]);
        $mockPaginator = new LengthAwarePaginator(
            $mockFeatureRequests,
            50, // total items
            $expectedPerPage,
            $expectedPage,
            ['path' => '/feature-requests']
        );

        // Expect the 'search' method on the mocked service to be called once
        $this->featureRequestServiceMock
            ->shouldReceive('search')
            ->once()
            ->with(
                $expectedFilters,
                $expectedSorting,
                $expectedPage,
                $expectedPerPage
            )
            ->andReturn($mockPaginator);

        // Ensure no error is logged on success
        Log::shouldNotReceive('error');

        // Act
        $result = $this->searchFeatureRequestAction->handle($mockRequest);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('featureRequestsPagination', $result);
        $this->assertArrayHasKey('filters', $result);
        $this->assertArrayHasKey('sorting', $result);

        $this->assertEquals($mockPaginator, $result['featureRequestsPagination']);
        $this->assertEquals($expectedFilters, $result['filters']);
        $this->assertEquals($expectedSorting, $result['sorting']);
    }

    #[Test]
    public function it_searches_feature_requests_successfully_with_default_parameters()
    {
        // Arrange
        $mockRequestData = []; // No specific filters or sorting
        $mockRequest = Request::create('/feature-requests', 'GET', $mockRequestData);

        $expectedFilters = []; // No filters
        $expectedSorting = []; // No sorting
        $expectedPage = 1; // Default page
        $expectedPerPage = 10; // Default perPage

        // Mock the paginated result from the service
        $mockFeatureRequests = collect([
            (object) ['id' => 3, 'title' => 'Feature C'],
        ]);
        $mockPaginator = new LengthAwarePaginator(
            $mockFeatureRequests,
            10, // total items
            $expectedPerPage,
            $expectedPage,
            ['path' => '/feature-requests']
        );

        $this->featureRequestServiceMock
            ->shouldReceive('search')
            ->once()
            ->with(
                $expectedFilters,
                $expectedSorting,
                $expectedPage,
                $expectedPerPage
            )
            ->andReturn($mockPaginator);

        Log::shouldNotReceive('error');

        // Act
        $result = $this->searchFeatureRequestAction->handle($mockRequest);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals($mockPaginator, $result['featureRequestsPagination']);
        $this->assertEquals($expectedFilters, $result['filters']);
        $this->assertEquals($expectedSorting, $result['sorting']);
    }

    #[Test]
    public function it_logs_and_rethrows_exception_if_service_fails()
    {
        // Arrange
        $mockRequestData = ['search' => 'error_case'];
        $mockRequest = Request::create('/feature-requests', 'GET', $mockRequestData);

        $exceptionMessage = 'Database connection failed during search.';
        $mockException = new \Exception($exceptionMessage);

        // Expect the 'search' method on the mocked service to throw an exception
        $this->featureRequestServiceMock
            ->shouldReceive('search')
            ->once()
            ->andThrow($mockException); // Simulate an error from the service

        // Expect an error to be logged
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($exceptionMessage) {
                return str_contains($message, 'Error searching feature request') &&
                       str_contains($message, $exceptionMessage) &&
                       isset($context['exception']) &&
                       $context['exception'] instanceof Throwable;
            });

        // Act & Assert: Expect the exception to be re-thrown by the action
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->searchFeatureRequestAction->handle($mockRequest);
    }
}
