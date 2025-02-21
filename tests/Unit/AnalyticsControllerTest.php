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
}
