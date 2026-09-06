<?php

namespace Tests\Feature\Admin;

class AdminAuthTest extends AdminFeatureTestCase
{
    public function test_admin_dashboard_redirects_a_guest_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
    }

    public function test_a_valid_admin_can_log_in(): void
    {
        $admin = $this->admin(['password' => bcrypt('secret-password')]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_incorrect_credentials_are_rejected(): void
    {
        $admin = $this->admin(['password' => bcrypt('secret-password')]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_an_authenticated_admin_can_log_out(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin');

        $response = $this->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }
}
