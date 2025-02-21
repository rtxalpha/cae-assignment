<?php

namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;
class ImportRosterTest extends TestCase
{
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

        $response = $this->postJson('/api/import-roster', [
            'roster' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true]);
    }
    public function test_import_roster_no_file()
    {
        $response = $this->postJson('/api/import-roster', [

        ]);

        $response->assertStatus(200) 
            ->assertJson([
                'status' => false,
                'errors' => [
                    "roster" => [
                        "The roster field is required."
                    ]
                ]
            ]);
    }
    public function test_import_roster_validation()
    {
        $response = $this->postJson('/api/import-roster', []);

        $response->assertStatus(200) 
            ->assertJson([
                'status' => false,
                'errors' => [
                    'roster' => [
                        "The roster field is required."
                    ]
                ]
            ]);
    }

}
