<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that the administration UI is rendered in Czech (locale, Filament strings, detail pages).
 */
class FilamentLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_locale_is_czech(): void
    {
        $this->assertSame('cs', app()->getLocale());
        $this->assertSame('Pole e-mail je povinné.', __('validation.required', ['attribute' => 'e-mail']));
    }

    public function test_login_page_uses_czech_filament_translations(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Přihlásit')
            ->assertDontSee('Sign in');
    }

    public function test_detail_pages_are_rendered_in_czech(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@akademietrutnov.cz')->firstOrFail();

        $pages = [
            '/admin/courses/' . Course::firstOrFail()->id => ['Kód kurzu', 'Hodinový rozsah'],
            '/admin/course-terms/' . CourseTerm::firstOrFail()->id => ['Stav termínu', 'Obsazenost'],
            '/admin/lecturers/' . Lecturer::firstOrFail()->id => ['Příjmení', 'Odborná specializace'],
            '/admin/lessons/' . Lesson::firstOrFail()->id => ['Vyučující lektor', 'Začátek lekce'],
            '/admin/students/' . Student::firstOrFail()->id => ['Příjmení', 'Registrován v systému'],
            '/admin/registrations/' . Registration::firstOrFail()->id => ['Stav přihlášky', 'Pořadí v pořadníku'],
            '/admin/certificates/' . Certificate::firstOrFail()->id => ['Číslo osvědčení', 'Absolvent'],
        ];

        if ($attendance = AttendanceRecord::first()) {
            $pages['/admin/attendance-records/' . $attendance->id] = ['Přihlášený student', 'Výuková lekce'];
        }

        foreach ($pages as $url => $expected) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertOk();
            foreach ($expected as $text) {
                $response->assertSee($text);
            }
            $response->assertDontSee('Course term');
            $response->assertDontSee('Email address');
            $response->assertDontSee('Updated at');
        }
    }
}
