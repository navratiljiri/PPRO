<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Services\StatisticService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StatisticService $statisticService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statisticService = app(StatisticService::class);
    }

    public function test_it_calculates_term_occupancy_statistics(): void
    {
        $course = Course::create([
            'code' => 'STAT-01',
            'name' => 'Kurz pro statistiky',
            'annotation' => 'Test',
            'duration_hours' => 20,
            'price' => 1000,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $term = CourseTerm::create([
            'course_id' => $course->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'classroom' => 'Učebna A',
            'capacity' => 10,
            'status' => 'enrolling',
        ]);

        $s1 = Student::create(['first_name' => 'A', 'last_name' => 'B', 'email' => 'ab@test.cz']);
        $s2 = Student::create(['first_name' => 'C', 'last_name' => 'D', 'email' => 'cd@test.cz']);

        Registration::create(['course_term_id' => $term->id, 'student_id' => $s1->id, 'status' => 'registered']);
        Registration::create(['course_term_id' => $term->id, 'student_id' => $s2->id, 'status' => 'registered']);

        $stats = $this->statisticService->getTermOccupancyStats();
        $termStat = $stats->firstWhere('id', $term->id);

        $this->assertNotNull($termStat);
        $this->assertEquals(2, $termStat->occupied_spots);
        $this->assertEquals(20.0, $termStat->occupancy_rate);
    }

    public function test_it_calculates_lecturer_taught_hours(): void
    {
        $course = Course::create([
            'code' => 'LEC-01',
            'name' => 'Kurz',
            'annotation' => 'Test',
            'duration_hours' => 20,
            'price' => 1000,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $term = CourseTerm::create([
            'course_id' => $course->id,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(10),
            'classroom' => 'Učebna',
            'capacity' => 10,
            'status' => 'enrolling',
        ]);

        $lecturer = Lecturer::create([
            'first_name' => 'Josef',
            'last_name' => 'Učitel',
            'email' => 'josef@test.cz',
        ]);

        Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'starts_at' => Carbon::now()->addDays(1),
            'ends_at' => Carbon::now()->addDays(1)->addHours(4),
            'duration_hours' => 4,
            'topic' => 'Lekce 1',
        ]);
        Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'starts_at' => Carbon::now()->addDays(2),
            'ends_at' => Carbon::now()->addDays(2)->addHours(6),
            'duration_hours' => 6,
            'topic' => 'Lekce 2',
        ]);

        $hoursList = $this->statisticService->getLecturerTaughtHours();
        $lecturerItem = $hoursList->firstWhere('id', $lecturer->id);

        $this->assertNotNull($lecturerItem);
        $this->assertEquals(10, $lecturerItem->total_taught_hours);
    }

    public function test_it_identifies_students_enrolled_in_multiple_courses(): void
    {
        $course1 = Course::create([
            'code' => 'MULTI-01',
            'name' => 'Kurz 1',
            'annotation' => 'Test 1',
            'duration_hours' => 10,
            'price' => 1000,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $course2 = Course::create([
            'code' => 'MULTI-02',
            'name' => 'Kurz 2',
            'annotation' => 'Test 2',
            'duration_hours' => 10,
            'price' => 1000,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $term1 = CourseTerm::create([
            'course_id' => $course1->id,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(5),
            'classroom' => 'Učebna 1',
            'capacity' => 10,
            'status' => 'enrolling',
        ]);

        $term2 = CourseTerm::create([
            'course_id' => $course2->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(15),
            'classroom' => 'Učebna 2',
            'capacity' => 10,
            'status' => 'enrolling',
        ]);

        $studentSingle = Student::create(['first_name' => 'Jediný', 'last_name' => 'Kurz', 'email' => 'single@test.cz']);
        $studentMulti = Student::create(['first_name' => 'Vícero', 'last_name' => 'Kurzů', 'email' => 'multi@test.cz']);

        Registration::create(['course_term_id' => $term1->id, 'student_id' => $studentSingle->id, 'status' => 'registered']);

        Registration::create(['course_term_id' => $term1->id, 'student_id' => $studentMulti->id, 'status' => 'registered']);
        Registration::create(['course_term_id' => $term2->id, 'student_id' => $studentMulti->id, 'status' => 'registered']);

        $currentYear = Carbon::now()->year;
        $multiStudents = $this->statisticService->getMultiCourseStudents($currentYear);

        $this->assertCount(1, $multiStudents);
        $this->assertEquals($studentMulti->id, $multiStudents->first()->id);
        $this->assertEquals(2, $multiStudents->first()->distinct_courses_count);
    }
}
