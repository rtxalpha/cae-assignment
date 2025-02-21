<?php

namespace Tests\Unit;

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
    public function test_flight_from_location_missing_data_validation()
    {
        $response = $this->json('GET', '/api/flights/from', [
            'location' => "",
        ]);

        $response->assertStatus(200) 
            ->assertJson([
                'status' => false,
                'errors' => [
                    'location' => [
                        "The location field is required."
                    ]
                ]
            ]);
    }
    public function test_flight_from_location_blank_validation()
    {
        $response = $this->json('GET', '/api/flights/from', [
            
        ]);

        $response->assertStatus(200) 
            ->assertJson([
                'status' => false,
                'errors' => [
                    'location' => [
                        "The location field is required."
                    ]
                ]
            ]);
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
    public function test_next_week_flight_from_current_date_missing_data_validation()
    {
        $response = $this->json('GET', '/api/flights/next-week', [
            'current_date' => ''
        ]);

        $response->assertStatus(200) 
            ->assertJson([
                'status' => false,
                'errors' => [
                    'current_date' => [
                        "The current date field is required."
                    ]
                ]
            ]);
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
    public function test_standy_events_from_current_date_missing_data_validation()
    {
        $response = $this->json('GET', '/api/events/standby', [
            'current_date' => ''
        ]);

        $response->assertStatus(200) 
            ->assertJson([
                'status' => false,
                'errors' => [
                    'current_date' => [
                        "The current date field is required."
                    ]
                ]
            ]);
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
    public function test_events_between_from_date_to_date_missing_start_date_validation()
    {
        $response = $this->json('GET', '/api/events', [
            'start_date' => "",
            'end_date' => "2022-01-14",
        ]);
        $response->assertStatus(200) 
            ->assertJson([
                'status' => false,
                'errors' => [
                    'start_date' => [
                        "The start date field is required."
                    ]
                ]
            ]);
    }
    
}
