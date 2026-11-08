<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response()
    {
        // Pas besoin des fichiers compilés par Vite pour ce test (sprint 0)
        $this->withoutVite();

        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
