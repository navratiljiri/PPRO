<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('Akademie Trutnov');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Správce Akademie',
            'email' => 'admin@akademietrutnov.cz',
            'password' => Hash::make('admin123'),
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Akademie Trutnov');
    }

    public function test_authenticated_admin_can_access_resources(): void
    {
        $admin = User::create([
            'name' => 'Správce Akademie',
            'email' => 'admin@akademietrutnov.cz',
            'password' => Hash::make('admin123'),
        ]);

        $this->actingAs($admin)->get('/admin/courses')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/course-terms')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/lecturers')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/lessons')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/students')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/registrations')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/attendance-records')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/certificates')->assertStatus(200);
    }
}
