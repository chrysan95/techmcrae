<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_passwords_are_stored_hashed(): void
    {
        $this->assertDatabaseMissing('employees', ['employee_id' => 'TMC0001', 'password' => 'password']);
    }

    public function test_login_with_employee_id(): void
    {
        $this->post('/login', ['login' => 'TMC0001', 'password' => 'password'])
            ->assertRedirect('/portal');
        $this->assertAuthenticated();
    }

    public function test_login_with_email(): void
    {
        $this->post('/login', ['login' => 'bruce@techmcrae.com', 'password' => 'password'])
            ->assertRedirect('/portal');
        $this->assertAuthenticated();
    }

    public function test_admin_redirects_to_dashboard(): void
    {
        $this->post('/login', ['login' => 'TMC0099', 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->post('/login', ['login' => 'TMC0001', 'password' => 'wrongpass'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_empty_input_is_rejected(): void
    {
        $this->post('/login', ['login' => '', 'password' => ''])
            ->assertSessionHasErrors(['login', 'password']);
    }
}