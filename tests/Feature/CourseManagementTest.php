<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_the_courses_catalog(): void
    {
        Course::create([
            'code' => 'TST-01',
            'name' => 'Testovací kurz účetnictví',
            'annotation' => 'Popis testovacího kurzu pro ověření katalogu.',
            'duration_hours' => 40,
            'price' => 8500.00,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $response = $this->get('/courses');

        $response->assertStatus(200);
        $response->assertSee('Katalog a správa kurzů');
        $response->assertSee('Testovací kurz účetnictví');
        $response->assertSee('TST-01');
    }

    public function test_it_filters_accredited_courses(): void
    {
        Course::create([
            'code' => 'AKR-01',
            'name' => 'Akreditovaný kurz MŠMT',
            'annotation' => 'Tento kurz má akreditaci.',
            'duration_hours' => 50,
            'price' => 12000.00,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        Course::create([
            'code' => 'NEAKR-01',
            'name' => 'Obyčejný firemní workshop',
            'annotation' => 'Tento kurz nemá akreditaci.',
            'duration_hours' => 10,
            'price' => 3000.00,
            'is_accredited' => false,
            'status' => 'active',
        ]);

        $response = $this->get('/courses?filter=accredited');

        $response->assertStatus(200);
        $response->assertSee('Akreditovaný kurz MŠMT');
        $response->assertDontSee('Obyčejný firemní workshop');
    }

    public function test_it_can_create_a_course_via_form(): void
    {
        $payload = [
            'code' => 'NOV-01',
            'name' => 'Nový kurz digitálních dovedností',
            'annotation' => 'Komplexní přehled digitálních nástrojů pro kancelář.',
            'duration_hours' => 30,
            'price' => 5900.00,
            'is_accredited' => '1',
        ];

        $response = $this->post('/courses', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'code' => 'NOV-01',
            'name' => 'Nový kurz digitálních dovedností',
            'is_accredited' => true,
        ]);
    }

    public function test_it_validates_required_fields_and_uniqueness(): void
    {
        Course::create([
            'code' => 'DUP-01',
            'name' => 'Původní kurz',
            'annotation' => 'Platná anotace původního kurzu.',
            'duration_hours' => 20,
            'price' => 4000.00,
            'is_accredited' => false,
            'status' => 'active',
        ]);

        // Attempt creation with duplicate code and missing name
        $response = $this->post('/courses', [
            'code' => 'DUP-01',
            'name' => '',
            'annotation' => 'Short',
            'duration_hours' => 0,
            'price' => -100,
        ]);

        $response->assertSessionHasErrors(['code', 'name', 'annotation', 'duration_hours', 'price']);
    }

    public function test_it_can_update_a_course(): void
    {
        $course = Course::create([
            'code' => 'UPD-01',
            'name' => 'Před úpravou',
            'annotation' => 'Stará anotace kurzu před úpravou.',
            'duration_hours' => 20,
            'price' => 5000.00,
            'is_accredited' => false,
            'status' => 'active',
        ]);

        $response = $this->put("/courses/{$course->id}", [
            'code' => 'UPD-01',
            'name' => 'Po úspěšné úpravě',
            'annotation' => 'Aktualizovaná a rozšířená anotace kurzu.',
            'duration_hours' => 25,
            'price' => 6000.00,
            'is_accredited' => '1',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Po úspěšné úpravě',
            'price' => 6000.00,
            'is_accredited' => true,
        ]);
    }

    public function test_it_can_delete_a_course(): void
    {
        $course = Course::create([
            'code' => 'DEL-01',
            'name' => 'Kurz ke smazání',
            'annotation' => 'Tento kurz bude bezpečně odstraněn.',
            'duration_hours' => 10,
            'price' => 2000.00,
            'is_accredited' => false,
            'status' => 'active',
        ]);

        $response = $this->delete("/courses/{$course->id}");

        $response->assertRedirect('/courses');
        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }
}
