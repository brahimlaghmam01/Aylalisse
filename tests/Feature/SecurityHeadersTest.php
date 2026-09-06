<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_public_pages(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
    }

    public function test_content_security_policy_header_is_present(): void
    {
        $response = $this->get('/');

        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("default-src 'self'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_debug_mode_disabled_does_not_leak_stack_traces_on_error(): void
    {
        config(['app.debug' => false]);

        Route::get('/__force-error-for-test', function () {
            throw new \RuntimeException('Erreur interne simulée avec un détail sensible');
        });

        $response = $this->get('/__force-error-for-test');

        $response->assertStatus(500);
        $response->assertDontSee('Erreur interne simulée avec un détail sensible');
        $response->assertDontSee('Stack trace', false);
        $response->assertSee('Une erreur est survenue.');
    }
}
