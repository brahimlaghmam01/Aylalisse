<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_route_returns_the_custom_404_page(): void
    {
        $response = $this->get('/cette-page-n-existe-pas-du-tout');

        $response->assertStatus(404);
        $response->assertSee('Cette page n’existe pas.');
    }

    public function test_exceeding_the_admin_login_rate_limit_returns_the_custom_429_page(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/admin/login', ['email' => 'nobody@example.com', 'password' => 'wrong']);
        }

        $response = $this->post('/admin/login', ['email' => 'nobody@example.com', 'password' => 'wrong']);

        $response->assertStatus(429);
        $response->assertSee('Un peu trop rapide.');
    }
}
