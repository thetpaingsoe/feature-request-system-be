<?php

namespace Tests\Unit\Services\FeatureRequest;

use App\Models\FeatureRequest;
use App\Services\FeatureRequest\FeatureRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeatureRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FeatureRequestService;
    }

    #[Test]
    public function it_can_search_feature_requests()
    {
        // Create test data
        $feature1 = FeatureRequest::factory()->create([
            'title' => 'Login feature',
            'email' => 'user1@example.com',
            'status' => 'pending',
            'submitted_at' => '2023-01-01',
        ]);

        $feature2 = FeatureRequest::factory()->create([
            'title' => 'Dashboard feature',
            'email' => 'user2@example.com',
            'status' => 'approved',
            'submitted_at' => '2023-01-15',
        ]);

        // Test search by title
        $results = $this->service->search(
            ['search' => 'Login'],
            [],
            1
        );
        $this->assertCount(1, $results);
        $this->assertEquals($feature1->id, $results->first()->id);

        // Test search by email
        $results = $this->service->search(
            ['search' => 'user2@example.com'],
            [],
            1
        );
        $this->assertCount(1, $results);
        $this->assertEquals($feature2->id, $results->first()->id);

        // Test status filter
        $results = $this->service->search(
            ['status' => 'approved'],
            [],
            1
        );
        $this->assertCount(1, $results);
        $this->assertEquals($feature2->id, $results->first()->id);

        // Test date range
        $results = $this->service->search(
            ['date_start' => '2023-01-10', 'date_end' => '2023-01-20'],
            [],
            1
        );
        $this->assertCount(1, $results);
        $this->assertEquals($feature2->id, $results->first()->id);

        // Test sorting
        $results = $this->service->search(
            [],
            ['sort_by' => 'title', 'sort_direction' => 'asc'],
            1
        );
        $this->assertEquals('Dashboard feature', $results->first()->title);
    }

    #[Test]
    public function it_can_get_a_feature_request()
    {
        $feature = FeatureRequest::factory()->create();

        $result = $this->service->get($feature->id);

        $this->assertInstanceOf(FeatureRequest::class, $result);
        $this->assertEquals($feature->id, $result->id);
    }

    #[Test]
    public function it_throws_exception_when_getting_nonexistent_feature()
    {
        Log::shouldReceive('error')->once();

        $this->expectException(\Throwable::class);

        $this->service->get(999);
    }

    #[Test]
    public function it_can_create_a_feature_request()
    {
        $data = (object) [
            'title' => 'New Feature',
            'description' => 'Feature description',
            'email' => 'test@example.com',
        ];

        $result = $this->service->create($data);

        $this->assertInstanceOf(FeatureRequest::class, $result);
        $this->assertEquals('New Feature', $result->title);
        $this->assertDatabaseHas('feature_requests', [
            'title' => 'New Feature',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_handles_creation_errors()
    {
        Log::shouldReceive('error')->once();

        $this->expectException(\Throwable::class);

        // Missing required fields
        $this->service->create((object) []);
    }

    #[Test]
    public function it_can_update_status()
    {
        $feature = FeatureRequest::factory()->create(['status' => 'pending']);

        $result = $this->service->updateStatus($feature, 'approved');

        $this->assertEquals('approved', $result->status);
        $this->assertDatabaseHas('feature_requests', [
            'id' => $feature->id,
            'status' => 'approved',
        ]);
    }

    #[Test]
    public function it_rejects_invalid_status_update()
    {
        $feature = FeatureRequest::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->updateStatus($feature, 'invalid_status');
    }

    #[Test]
    public function it_can_update_note()
    {
        $feature = FeatureRequest::factory()->create(['note' => null]);

        $result = $this->service->updateNote($feature, 'New note');

        $this->assertEquals('New note', $result->note);
        $this->assertDatabaseHas('feature_requests', [
            'id' => $feature->id,
            'note' => 'New note',
        ]);
    }

    #[Test]
    public function it_can_set_note_to_null()
    {
        $feature = FeatureRequest::factory()->create(['note' => 'Existing note']);

        $result = $this->service->updateNote($feature, null);

        $this->assertNull($result->note);
    }

    #[Test]
    public function it_can_delete_a_feature_request()
    {
        $feature = FeatureRequest::factory()->create();

        $result = $this->service->delete($feature);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('feature_requests', ['id' => $feature->id]);
    }
}
