<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_is_accessible_for_guests(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_public_home_page_is_accessible(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
