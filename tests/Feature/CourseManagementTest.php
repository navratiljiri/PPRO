<?php

namespace Tests\Feature;

use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public catalog is read-only; course administration happens only in Filament (/admin).
 */
class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function course(array $overrides = []): Course
    {
        return Course::create(array_merge([
            'code' => 'TST-01',
            'name' => 'Testovací kurz účetnictví',
            'annotation' => 'Popis testovacího kurzu pro ověření katalogu.',
            'duration_hours' => 40,
            'price' => 8500.00,
            'is_accredited' => true,
            'status' => 'active',
        ], $overrides));
    }

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::create([
            'name' => 'Správce',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
        ]));
    }

    public function test_it_displays_the_courses_catalog(): void
    {
        $this->course();

        $response = $this->get('/courses');

        $response->assertStatus(200);
        $response->assertSee('Katalog kurzů');
        $response->assertSee('Testovací kurz účetnictví');
        $response->assertSee('TST-01');
        $response->assertDontSee('Vytvořit nový kurz');
        $response->assertDontSee('Upravit');
    }

    public function test_it_filters_accredited_courses(): void
    {
        $this->course(['code' => 'AKR-01', 'name' => 'Akreditovaný kurz MŠMT']);
        $this->course(['code' => 'NEAKR-01', 'name' => 'Obyčejný firemní workshop', 'is_accredited' => false]);

        $response = $this->get('/courses?filter=accredited');

        $response->assertStatus(200);
        $response->assertSee('Akreditovaný kurz MŠMT');
        $response->assertDontSee('Obyčejný firemní workshop');
    }

    public function test_public_website_has_no_course_administration(): void
    {
        $course = $this->course();

        $this->get('/courses/create')->assertNotFound();
        $this->get("/courses/{$course->id}/edit")->assertNotFound();
        $this->post('/courses', ['code' => 'X'])->assertStatus(405);
        $this->put("/courses/{$course->id}", [])->assertStatus(405);
        $this->delete("/courses/{$course->id}")->assertStatus(405);

        $this->get("/courses/{$course->id}")
            ->assertOk()
            ->assertDontSee('Upravit kurz')
            ->assertDontSee('Smazat');
    }

    public function test_admin_can_create_a_course_in_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCourse::class)
            ->fillForm([
                'code' => 'nov-01',
                'name' => 'Nový kurz digitálních dovedností',
                'annotation' => 'Komplexní přehled digitálních nástrojů pro kancelář.',
                'duration_hours' => 30,
                'price' => 5900,
                'is_accredited' => true,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // CourseService normalises the code to upper case
        $this->assertDatabaseHas('courses', [
            'code' => 'NOV-01',
            'name' => 'Nový kurz digitálních dovedností',
            'is_accredited' => true,
        ]);
    }

    public function test_filament_validates_required_fields_and_uniqueness(): void
    {
        $this->actingAsAdmin();
        $this->course(['code' => 'DUP-01']);

        Livewire::test(CreateCourse::class)
            ->fillForm([
                'code' => 'DUP-01',
                'name' => '',
                'annotation' => 'Platná anotace',
                'duration_hours' => 0,
                'price' => -100,
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasFormErrors(['code', 'name', 'duration_hours', 'price']);

        $this->assertSame(1, Course::count());
    }

    public function test_admin_can_update_a_course_in_filament(): void
    {
        $this->actingAsAdmin();
        $course = $this->course(['code' => 'UPD-01', 'name' => 'Před úpravou', 'is_accredited' => false]);

        Livewire::test(EditCourse::class, ['record' => $course->getRouteKey()])
            ->fillForm([
                'name' => 'Po úspěšné úpravě',
                'price' => 6000,
                'is_accredited' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Po úspěšné úpravě',
            'price' => 6000.00,
            'is_accredited' => true,
        ]);
    }
}
