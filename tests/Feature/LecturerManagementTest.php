<?php

namespace Tests\Feature;

use App\Filament\Resources\Lecturers\Pages\CreateLecturer;
use App\Filament\Resources\Lecturers\Pages\EditLecturer;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public lecturer list is read-only; lecturer administration happens only in Filament (/admin).
 */
class LecturerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Správce',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
        ]));
    }

    private function lecturer(array $overrides = []): Lecturer
    {
        return Lecturer::create(array_merge([
            'first_name' => 'Jan',
            'last_name' => 'Novák',
            'email' => 'jan.novak@example.cz',
        ], $overrides));
    }

    public function test_it_displays_the_lecturers_catalog(): void
    {
        $this->lecturer(['phone' => '+420 777 123 456', 'specialization' => 'Finanční účetnictví a daně']);

        $response = $this->get('/lecturers');

        $response->assertStatus(200);
        $response->assertSee('Jan Novák');
        $response->assertSee('jan.novak@example.cz');
    }

    public function test_public_website_has_no_lecturer_administration(): void
    {
        $lecturer = $this->lecturer();

        $this->get('/lecturers/create')->assertNotFound();
        $this->get("/lecturers/{$lecturer->id}/edit")->assertNotFound();
        $this->post('/lecturers', [])->assertStatus(405);
        $this->delete("/lecturers/{$lecturer->id}")->assertStatus(405);
    }

    public function test_admin_can_create_a_lecturer_in_filament(): void
    {
        Livewire::test(CreateLecturer::class)
            ->fillForm([
                'first_name' => 'Petra',
                'last_name' => 'Dvořáková',
                'email' => 'Petra.Dvorakova@example.cz',
                'phone' => '+420 602 111 222',
                'specialization' => 'Projektové řízení',
                'bio' => 'Zkušená lektorka s 10letou praxí.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // LecturerService normalises the e-mail to lower case
        $this->assertDatabaseHas('lecturers', [
            'first_name' => 'Petra',
            'last_name' => 'Dvořáková',
            'email' => 'petra.dvorakova@example.cz',
        ]);
    }

    public function test_filament_validates_unique_email_for_lecturers(): void
    {
        $this->lecturer(['email' => 'karel@example.cz']);

        Livewire::test(CreateLecturer::class)
            ->fillForm([
                'first_name' => 'Jiný',
                'last_name' => 'Karel',
                'email' => 'karel@example.cz',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);

        $this->assertSame(1, Lecturer::count());
    }

    public function test_admin_can_update_a_lecturer_in_filament(): void
    {
        $lecturer = $this->lecturer(['first_name' => 'Tomáš', 'last_name' => 'Marný', 'email' => 'tomas@example.cz']);

        Livewire::test(EditLecturer::class, ['record' => $lecturer->getRouteKey()])
            ->fillForm([
                'last_name' => 'Moudrý',
                'email' => 'tomas.moudry@example.cz',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

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
        $lecturer = $this->lecturer(['first_name' => 'Eva', 'last_name' => 'Svobodová', 'email' => 'eva@example.cz']);
        Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'topic' => 'Základy',
            'starts_at' => now()->addDays(5)->setTime(9, 0),
            'ends_at' => now()->addDays(5)->setTime(13, 0),
            'duration_hours' => 4,
        ]);

        Livewire::test(EditLecturer::class, ['record' => $lecturer->getRouteKey()])
            ->callAction(DeleteAction::class)
            ->assertNotified('Lektora nelze smazat');

        $this->assertDatabaseHas('lecturers', ['id' => $lecturer->id]);
        $this->assertSame(1, Lesson::count());
    }

    public function test_it_can_delete_lecturer_without_lessons(): void
    {
        $lecturer = $this->lecturer(['email' => 'martin@example.cz']);

        Livewire::test(EditLecturer::class, ['record' => $lecturer->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertDatabaseMissing('lecturers', ['id' => $lecturer->id]);
    }
}
