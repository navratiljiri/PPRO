<?php

namespace Tests\Feature;

use App\Filament\Resources\CourseTerms\Pages\CreateCourseTerm;
use App\Filament\Resources\CourseTerms\Pages\EditCourseTerm;
use App\Filament\Resources\Lessons\Pages\CreateLesson;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use App\Models\User;
use App\Services\CourseTermService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Public term list is read-only; term and lesson administration happens only in Filament (/admin).
 */
class CourseTermManagementTest extends TestCase
{
    use RefreshDatabase;

    private Course $activeCourse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeCourse = Course::create([
            'code' => 'ACC-101',
            'name' => 'Podvojné účetnictví',
            'annotation' => 'Komplexní kurz podvojného účetnictví.',
            'duration_hours' => 60,
            'price' => 12500,
            'is_accredited' => true,
            'status' => 'active',
        ]);
    }

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::create([
            'name' => 'Správce',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
        ]));
    }

    private function term(array $overrides = []): CourseTerm
    {
        return CourseTerm::create(array_merge([
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'classroom' => 'Učebna 101',
            'capacity' => 10,
            'status' => 'enrolling',
        ], $overrides));
    }

    public function test_it_displays_the_terms_list(): void
    {
        $this->term(['classroom' => 'Učebna 204 (Počítačová)', 'capacity' => 12]);

        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Termíny kurzů');
        $response->assertSee('Učebna 204 (Počítačová)');
        $response->assertSee('Podvojné účetnictví');
    }

    public function test_public_website_has_no_term_administration(): void
    {
        $term = $this->term();

        $this->get('/terms/create')->assertNotFound();
        $this->get("/terms/{$term->id}/edit")->assertNotFound();
        $this->post('/terms', [])->assertStatus(405);
        $this->delete("/terms/{$term->id}")->assertStatus(405);
        $this->post("/terms/{$term->id}/lessons", [])->assertNotFound();

        $this->get("/terms/{$term->id}")
            ->assertOk()
            ->assertDontSee('Upravit termín')
            ->assertDontSee('Přidat výukový blok');
    }

    public function test_admin_can_create_a_course_term_in_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCourseTerm::class)
            ->fillForm([
                'course_id' => $this->activeCourse->id,
                'start_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'end_date' => now()->addDays(15)->format('Y-m-d H:i:s'),
                'classroom' => 'Učebna 101',
                'capacity' => 16,
                'status' => 'enrolling',
                'note' => 'Termín s ranní výukou.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('course_terms', [
            'course_id' => $this->activeCourse->id,
            'classroom' => 'Učebna 101',
            'capacity' => 16,
            'status' => 'enrolling',
        ]);
    }

    public function test_term_service_validates_dates_in_filament(): void
    {
        $this->actingAsAdmin();

        Livewire::test(CreateCourseTerm::class)
            ->fillForm([
                'course_id' => $this->activeCourse->id,
                'start_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'end_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'classroom' => 'Učebna 101',
                'capacity' => 5,
                'status' => 'enrolling',
            ])
            ->call('create')
            ->assertHasFormErrors(['end_date']);

        $this->assertSame(0, CourseTerm::count());
    }

    public function test_admin_can_update_a_course_term_in_filament(): void
    {
        $this->actingAsAdmin();
        $term = $this->term(['classroom' => 'Učebna Stará']);

        Livewire::test(EditCourseTerm::class, ['record' => $term->getRouteKey()])
            ->fillForm([
                'classroom' => 'Učebna Nová',
                'capacity' => 14,
                'status' => 'in_progress',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('course_terms', [
            'id' => $term->id,
            'classroom' => 'Učebna Nová',
            'capacity' => 14,
            'status' => 'in_progress',
        ]);
    }

    public function test_lessons_support_alternating_lecturers(): void
    {
        $this->actingAsAdmin();
        $term = $this->term();
        $lecturerA = Lecturer::create(['first_name' => 'Karel', 'last_name' => 'Nový', 'email' => 'karel.novy@example.cz']);
        $lecturerB = Lecturer::create(['first_name' => 'Hana', 'last_name' => 'Bílá', 'email' => 'hana.bila@example.cz']);

        // Lesson 1 taught by lecturer A (created in Filament through CourseTermService)
        Livewire::test(CreateLesson::class)
            ->fillForm([
                'course_term_id' => $term->id,
                'lecturer_id' => $lecturerA->id,
                'topic' => 'Úvod a legislativa',
                'starts_at' => now()->addDays(10)->setTime(9, 0)->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(10)->setTime(13, 0)->format('Y-m-d H:i:s'),
                'duration_hours' => 4,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Lesson 2 taught by lecturer B (Rozhodnutí 5: alternating lecturers)
        app(CourseTermService::class)->addLessonToTerm($term, [
            'lecturer_id' => $lecturerB->id,
            'topic' => 'Praktické účtování v SW',
            'starts_at' => now()->addDays(12)->setTime(9, 0),
            'ends_at' => now()->addDays(12)->setTime(15, 0),
            'duration_hours' => 6,
        ]);

        $this->assertDatabaseHas('lessons', ['course_term_id' => $term->id, 'lecturer_id' => $lecturerA->id, 'duration_hours' => 4]);
        $this->assertDatabaseHas('lessons', ['course_term_id' => $term->id, 'lecturer_id' => $lecturerB->id, 'duration_hours' => 6]);
        $this->assertEquals(2, $term->fresh()->lecturers()->count());

        $lesson = Lesson::where('lecturer_id', $lecturerA->id)->firstOrFail();
        app(CourseTermService::class)->removeLesson($lesson);

        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
        $this->assertEquals(1, $term->fresh()->lecturers()->count());
    }

    public function test_lesson_ending_before_start_is_rejected_in_filament(): void
    {
        $this->actingAsAdmin();
        $term = $this->term();
        $lecturer = Lecturer::create(['first_name' => 'Karel', 'last_name' => 'Nový', 'email' => 'karel.novy@example.cz']);

        Livewire::test(CreateLesson::class)
            ->fillForm([
                'course_term_id' => $term->id,
                'lecturer_id' => $lecturer->id,
                'topic' => 'Chybná lekce',
                'starts_at' => now()->addDays(10)->setTime(13, 0)->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(10)->setTime(9, 0)->format('Y-m-d H:i:s'),
                'duration_hours' => 4,
            ])
            ->call('create')
            ->assertHasFormErrors(['ends_at']);

        $this->assertSame(0, Lesson::count());
    }
}
