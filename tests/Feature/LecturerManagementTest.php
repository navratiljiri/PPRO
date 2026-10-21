<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_the_lecturers_catalog(): void
    {
        Lecturer::create([
            'first_name' => 'Jan',
            'last_name' => 'Novák',
            'email' => 'jan.novak@akademietrutnov.cz',
            'phone' => '+420 777 123 456',
            'specialization' => 'Finanční účetnictví a daně',
        ]);

        $response = $this->get('/lecturers');

        $response->assertStatus(200);
        $response->assertSee('Jan Novák');
        $response->assertSee('jan.novak@akademietrutnov.cz');
    }

    public function test_it_can_create_a_lecturer_via_form(): void
    {
        $payload = [
            'first_name' => 'Petra',
            'last_name' => 'Dvořáková',
            'email' => 'petra.dvorakova@example.cz',
            'phone' => '+420 602 111 222',
            'specialization' => 'Projektové řízení',
            'bio' => 'Zkušená lektorka s 10letou praxí.',
        ];

        $response = $this->post('/lecturers', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('lecturers', [
            'first_name' => 'Petra',
            'last_name' => 'Dvořáková',
            'email' => 'petra.dvorakova@example.cz',
        ]);
    }

    public function test_it_validates_unique_email_for_lecturers(): void
    {
        Lecturer::create([
            'first_name' => 'Karel',
            'last_name' => 'Černý',
            'email' => 'karel@example.cz',
            'status' => 'active',
        ]);

        $response = $this->post('/lecturers', [
            'first_name' => 'Jiný',
            'last_name' => 'Karel',
            'email' => 'karel@example.cz',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_it_can_update_a_lecturer(): void
    {
        $lecturer = Lecturer::create([
            'first_name' => 'Tomáš',
            'last_name' => 'Marný',
            'email' => 'tomas@example.cz',
            'status' => 'active',
        ]);

        $response = $this->put("/lecturers/{$lecturer->id}", [
            'first_name' => 'Tomáš',
            'last_name' => 'Moudrý',
            'email' => 'tomas.moudry@example.cz',
            'phone' => '+420 777 999 888',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lecturers', [
            'id' => $lecturer->id,
            'last_name' => 'Moudrý',
            'email' => 'tomas.moudry@example.cz',
        ]);
    }

    public function test_it_prevents_deleting_lecturer_with_assigned_lessons(): void
    {
        $course = Course::create([
            'code' => 'TEST-01',
            'name' => 'Test Kurz',
            'annotation' => 'Test',
            'duration_hours' => 20,
            'price' => 5000,
            'status' => 'active',
        ]);

        $term = CourseTerm::create([
            'course_id' => $course->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'classroom' => 'Učebna A',
            'capacity' => 15,
            'status' => 'enrolling',
        ]);

        $lecturer = Lecturer::create([
            'first_name' => 'Eva',
            'last_name' => 'Svobodová',
            'email' => 'eva@example.cz',
            'status' => 'active',
        ]);

        Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'topic' => 'Základy',
            'starts_at' => now()->addDays(5)->setTime(9, 0),
            'ends_at' => now()->addDays(5)->setTime(13, 0),
            'duration_hours' => 4,
        ]);

        $response = $this->delete("/lecturers/{$lecturer->id}");

        $response->assertSessionHasErrors('lecturer');
        $this->assertDatabaseHas('lecturers', ['id' => $lecturer->id]);
    }

    public function test_it_can_delete_lecturer_without_lessons(): void
    {
        $lecturer = Lecturer::create([
            'first_name' => 'Martin',
            'last_name' => 'Volný',
            'email' => 'martin@example.cz',
            'status' => 'active',
        ]);

        $response = $this->delete("/lecturers/{$lecturer->id}");

        $response->assertRedirect('/lecturers');
        $this->assertDatabaseMissing('lecturers', ['id' => $lecturer->id]);
    }
}
