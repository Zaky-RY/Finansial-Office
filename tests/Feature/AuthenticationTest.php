<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_login_screen(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Finance');
        $response->assertSee('Masuk ke workspace');
    }

    public function test_active_user_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'employee@office.test',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'email' => 'employee@office.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'employee@office.test',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'employee@office.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_user_cannot_authenticate_with_unregistered_email(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'unknown@office.test',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'inactive@office.test',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive@office.test',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Akun Anda berstatus nonaktif. Silakan hubungi administrator.']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_protected_routes_cannot_be_accessed_without_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseFinance = $this->get('/finance/workspace');
        $responseFinance->assertRedirect('/login');

        $responseManagement = $this->get('/management/approvals');
        $responseManagement->assertRedirect('/login');

        $responseAdmin = $this->get('/admin/settings');
        $responseAdmin->assertRedirect('/login');
    }

    public function test_employee_cannot_access_finance_workspace(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
            'status' => 'active',
        ]);

        $response = $this->actingAs($employee)->get('/finance/workspace');

        $response->assertStatus(403);
    }

    public function test_roles_access_permissions_according_to_rules(): void
    {
        $finance = User::factory()->create(['role' => 'finance', 'status' => 'active']);
        $manager = User::factory()->create(['role' => 'manager', 'status' => 'active']);
        $director = User::factory()->create(['role' => 'director', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        // Finance can access finance workspace
        $this->actingAs($finance)->get('/finance/workspace')->assertStatus(200);

        // Manager and Director can access management approvals
        $this->actingAs($manager)->get('/management/approvals')->assertStatus(200);
        $this->actingAs($director)->get('/management/approvals')->assertStatus(200);

        // Director can access director overview
        $this->actingAs($director)->get('/director/overview')->assertStatus(200);

        // Admin can access admin settings
        $this->actingAs($admin)->get('/admin/settings')->assertStatus(200);

        // Manager cannot access admin settings
        $this->actingAs($manager)->get('/admin/settings')->assertStatus(403);
    }

    public function test_password_is_not_exposed_in_response_or_session(): void
    {
        User::factory()->create([
            'email' => 'employee@office.test',
            'password' => Hash::make('secret_password_123'),
            'status' => 'active',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'employee@office.test',
            'password' => 'secret_password_123',
        ]);

        $response->assertDontSee('secret_password_123');

        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertDontSee('secret_password_123');
    }

    public function test_database_seeder_runs_successfully(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@office.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'director@office.test', 'role' => 'director']);
        $this->assertDatabaseHas('users', ['email' => 'finance@office.test', 'role' => 'finance']);
        $this->assertDatabaseHas('users', ['email' => 'manager@office.test', 'role' => 'manager']);
        $this->assertDatabaseHas('users', ['email' => 'employee@office.test', 'role' => 'employee']);
        $this->assertDatabaseHas('departments', ['code' => 'TECH']);
        $this->assertDatabaseHas('categories', ['code' => 'TRP', 'type' => 'expense']);
    }
}
