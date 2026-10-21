<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

    public function test_it_displays_the_terms_list(): void
    {
        CourseTerm::create([
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'classroom' => 'Učebna 204 (Počítačová)',
            'capacity' => 12,
            'status' => 'enrolling',
        ]);

        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Termíny kurzů');
        $response->assertSee('Učebna 204 (Počítačová)');
        $response->assertSee('Podvojné účetnictví');
    }

    public function test_it_can_create_a_course_term(): void
    {
        $payload = [
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'end_date' => now()->addDays(15)->format('Y-m-d\TH:i'),
            'classroom' => 'Učebna 101',
            'capacity' => 16,
            'status' => 'enrolling',
            'note' => 'Termín s ranní výukou.',
        ];

        $response = $this->post('/terms', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_terms', [
            'course_id' => $this->activeCourse->id,
            'classroom' => 'Učebna 101',
            'capacity' => 16,
            'status' => 'enrolling',
        ]);
    }

    public function test_it_validates_dates_and_capacity(): void
    {
        // End date before start date
        $response = $this->post('/terms', [
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'end_date' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'classroom' => 'Učebna 101',
            'capacity' => 0,
        ]);

        $response->assertSessionHasErrors(['end_date', 'capacity']);
    }

    public function test_it_can_update_a_course_term(): void
    {
        $term = CourseTerm::create([
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'classroom' => 'Učebna Stará',
            'capacity' => 10,
            'status' => 'enrolling',
        ]);

        $response = $this->put("/terms/{$term->id}", [
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10)->format('Y-m-d\TH:i'),
            'end_date' => now()->addDays(20)->format('Y-m-d\TH:i'),
            'classroom' => 'Učebna Nová',
            'capacity' => 14,
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_terms', [
            'id' => $term->id,
            'classroom' => 'Učebna Nová',
            'capacity' => 14,
            'status' => 'in_progress',
        ]);
    }

    public function test_it_can_add_and_remove_lessons_to_support_alternating_lecturers(): void
    {
        $term = CourseTerm::create([
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'classroom' => 'Učebna 101',
            'capacity' => 15,
            'status' => 'enrolling',
        ]);

        $lecturerA = Lecturer::create([
            'first_name' => 'Karel',
            'last_name' => 'Nový',
            'email' => 'karel.novy@example.cz',
        ]);

        $lecturerB = Lecturer::create([
            'first_name' => 'Hana',
            'last_name' => 'Bílá',
            'email' => 'hana.bila@example.cz',
        ]);

        // Add lesson 1 taught by lecturer A
        $responseA = $this->post("/terms/{$term->id}/lessons", [
            'lecturer_id' => $lecturerA->id,
            'topic' => 'Úvod a legislativa',
            'starts_at' => now()->addDays(10)->setTime(9, 0)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(10)->setTime(13, 0)->format('Y-m-d\TH:i'),
            'duration_hours' => 4,
            'room' => '101',
        ]);

        $responseA->assertRedirect();
        $this->assertDatabaseHas('lessons', [
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturerA->id,
            'duration_hours' => 4,
        ]);

        // Add lesson 2 taught by lecturer B (Decision 5: alternating lecturers)
        $responseB = $this->post("/terms/{$term->id}/lessons", [
            'lecturer_id' => $lecturerB->id,
            'topic' => 'Praktické účtování v SW',
            'starts_at' => now()->addDays(12)->setTime(9, 0)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(12)->setTime(15, 0)->format('Y-m-d\TH:i'),
            'duration_hours' => 6,
            'room' => '101',
        ]);

        $responseB->assertRedirect();
        $this->assertDatabaseHas('lessons', [
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturerB->id,
            'duration_hours' => 6,
        ]);

        // Verify M:N relationship
        $this->assertEquals(2, $term->fresh()->lecturers()->count());

        // Remove lesson
        $lesson = Lesson::where('lecturer_id', $lecturerA->id)->first();
        $responseDelete = $this->delete("/lessons/{$lesson->id}");

        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
        $this->assertEquals(1, $term->fresh()->lecturers()->count());
    }

    public function test_it_can_delete_a_course_term(): void
    {
        $term = CourseTerm::create([
            'course_id' => $this->activeCourse->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
            'classroom' => 'Učebna 101',
            'capacity' => 10,
            'status' => 'enrolling',
        ]);

        $response = $this->delete("/terms/{$term->id}");

        $response->assertRedirect("/courses/{$this->activeCourse->id}");
        $this->assertDatabaseMissing('course_terms', ['id' => $term->id]);
    }
}
