<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class AnalyticsControllerTest extends TestCase
{
    public function test_flight_from_location()
    {
        $response = $this->json('GET', '/api/flights/from', [
            'location' => "KRP",
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['data']);
    }
    public function test_next_week_flight_from_current_date()
    {
        $response = $this->json('GET', '/api/flights/next-week', [
            'current_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['data']);
    }
    public function test_standy_events_from_current_date()
    {
        $response = $this->json('GET', '/api/events/standby', [
            'current_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['data']);
    }
    public function test_events_between_from_date_to_date()
    {
        $response = $this->json('GET', '/api/events', [
            'start_date' => "2022-01-10",
            'end_date' => "2022-01-14",
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['data']);
    }

    public function test_import_roster()
    {
    $filePath = storage_path('app/roster.html'); 

    if (!file_exists($filePath)) {
        $this->fail("Test file not found at: $filePath");
    }

    $file = new UploadedFile(
        $filePath,                  
        'roster.html',              
        'text/html',              
        null,                      
        true                       
    );

    // Send the request with the actual file
    $response = $this->postJson('/api/import-roster', [
        'roster' => $file,
    ]);

    // Assertions
    $response->assertStatus(200)
             ->assertJson(['status' => true]);
    }
}
