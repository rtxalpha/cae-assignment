<?php

namespace Tests\Unit;

use Tests\TestCase; 
class AnalyticsControllerTest extends TestCase
{
    public function test_next_week_flight_from_current_date()
    {
        $response = $this->json('GET', '/api/flights/next-week', [
            'current_date' => "2022-01-14",
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => true])
                 ->assertJsonStructure(['data']);
    }
    public function test_standy_events_from_current_date()
    {
        $response = $this->json('GET', '/api/events/standby', [
            'current_date' => "2022-01-14",
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

}
