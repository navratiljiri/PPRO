<?php

namespace Tests\Feature;

use App\Filament\Resources\CourseTerms\Pages\CourseTermGraduates;
use App\Filament\Resources\CourseTerms\Pages\EditCourseTerm;
use App\Filament\Widgets\Statistics\LecturerTaughtHoursTable;
use App\Filament\Widgets\Statistics\MultiCourseStudentsTable;
use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\CourseTermService;
use App\Services\RegistrationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Capacity integrity in the administration + graduates list and statistics pages.
 */
class AdminIntegrityAndReportsTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Správce',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
        ]));

        $this->course = Course::create([
            'code' => 'INT-01',
            'name' => 'Integritní kurz',
            'annotation' => 'Test',
            'duration_hours' => 8,
            'price' => 1000,
            'is_accredited' => false,
            'status' => 'active',
        ]);
    }

    private function makeTerm(int $capacity, ?Course $course = null): CourseTerm
    {
        return CourseTerm::create([
            'course_id' => ($course ?? $this->course)->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(12),
            'classroom' => 'Učebna A',
            'capacity' => $capacity,
            'status' => 'enrolling',
        ]);
    }

    private function makeStudent(string $suffix): Student
    {
        return Student::create([
            'first_name' => 'Student',
            'last_name' => "Číslo {$suffix}",
            'email' => "student{$suffix}@example.test",
        ]);
    }

    private function termData(CourseTerm $term, int $capacity): array
    {
        return [
            'course_id' => $term->course_id,
            'start_date' => $term->start_date->toDateTimeString(),
            'end_date' => $term->end_date->toDateTimeString(),
            'classroom' => $term->classroom,
            'capacity' => $capacity,
            'status' => $term->status,
        ];
    }

    // --- Registration cannot be moved to another term ---------------------------------

    public function test_registration_edit_page_does_not_exist(): void
    {
        $registration = app(RegistrationService::class)->registerStudent($this->makeTerm(5), $this->makeStudent('1'));

        $this->get("/admin/registrations/{$registration->id}/edit")->assertNotFound();
    }

    public function test_registration_cannot_be_moved_to_another_term_at_model_level(): void
    {
        $full = $this->makeTerm(1);
        $registration = app(RegistrationService::class)->registerStudent($this->makeTerm(5), $this->makeStudent('1'));
        app(RegistrationService::class)->registerStudent($full, $this->makeStudent('2'));

        $this->expectException(\RuntimeException::class);
        $registration->update(['course_term_id' => $full->id]);
    }

    // --- Capacity cannot drop below occupied spots -----------------------------------

    public function test_capacity_cannot_be_lowered_below_occupied_spots(): void
    {
        $term = $this->makeTerm(3);
        foreach (['1', '2', '3'] as $s) {
            app(RegistrationService::class)->registerStudent($term, $this->makeStudent($s));
        }

        try {
            app(CourseTermService::class)->updateTerm($term, $this->termData($term, 2));
            $this->fail('Capacity below occupied spots must be rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('capacity', $e->errors());
        }

        $this->assertSame(3, $term->fresh()->capacity);
    }

    public function test_capacity_cannot_be_lowered_in_filament_edit_form(): void
    {
        $term = $this->makeTerm(2);
        app(RegistrationService::class)->registerStudent($term, $this->makeStudent('1'));
        app(RegistrationService::class)->registerStudent($term, $this->makeStudent('2'));

        Livewire::test(EditCourseTerm::class, ['record' => $term->getRouteKey()])
            ->fillForm(['capacity' => 1])
            ->call('save')
            ->assertHasFormErrors(['capacity']);

        $this->assertSame(2, $term->fresh()->capacity);
    }

    public function test_capacity_increase_offers_spots_to_waiting_list_in_fifo_order(): void
    {
        $term = $this->makeTerm(1);
        $service = app(RegistrationService::class);
        $service->registerStudent($term, $this->makeStudent('1'));
        $first = $service->registerStudent($term, $this->makeStudent('2'));
        $second = $service->registerStudent($term, $this->makeStudent('3'));
        $third = $service->registerStudent($term, $this->makeStudent('4'));

        app(CourseTermService::class)->updateTerm($term, $this->termData($term, 3));

        $this->assertSame('offered', $first->fresh()->status);
        $this->assertSame('offered', $second->fresh()->status);
        $this->assertSame('waiting_list', $third->fresh()->status);
    }

    // --- Graduates list ----------------------------------------------------------------

    public function test_graduates_page_lists_students_with_results(): void
    {
        $term = $this->makeTerm(5);
        $lecturer = Lecturer::create(['first_name' => 'Petr', 'last_name' => 'Lektor', 'email' => 'petr@example.test']);
        $lessons = collect(range(1, 2))->map(fn ($i) => Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'starts_at' => Carbon::now()->addDays(10 + $i),
            'ends_at' => Carbon::now()->addDays(10 + $i)->addHours(4),
            'duration_hours' => 4,
            'topic' => "Lekce {$i}",
        ]));

        $good = Registration::create(['course_term_id' => $term->id, 'student_id' => $this->makeStudent('A')->id, 'status' => 'registered']);
        $bad = Registration::create(['course_term_id' => $term->id, 'student_id' => $this->makeStudent('B')->id, 'status' => 'registered']);
        foreach ($lessons as $lesson) {
            AttendanceRecord::create(['registration_id' => $good->id, 'lesson_id' => $lesson->id, 'is_present' => true]);
            AttendanceRecord::create(['registration_id' => $bad->id, 'lesson_id' => $lesson->id, 'is_present' => false]);
        }
        $certificate = app(CertificateService::class)->issueCertificate($good);

        $this->get("/admin/course-terms/{$term->id}/graduates")
            ->assertOk()
            ->assertSee('Absolventi')
            ->assertSee('Číslo A')
            ->assertSee($certificate->certificate_number)
            ->assertSee('Úspěšný absolvent')
            ->assertSee('Nesplňuje docházku');

        Livewire::test(CourseTermGraduates::class, ['record' => $term->getRouteKey()])
            ->filterTable('result', CourseTermGraduates::RESULT_GRADUATED)
            ->assertSee('Číslo A')
            ->assertDontSee('Číslo B');
    }

    public function test_certificate_can_be_issued_from_graduates_page(): void
    {
        $term = $this->makeTerm(5);
        $lecturer = Lecturer::create(['first_name' => 'Petr', 'last_name' => 'Lektor', 'email' => 'petr@example.test']);
        $lesson = Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'starts_at' => Carbon::now()->addDays(11),
            'ends_at' => Carbon::now()->addDays(11)->addHours(4),
            'duration_hours' => 4,
            'topic' => 'Lekce',
        ]);
        $reg = Registration::create(['course_term_id' => $term->id, 'student_id' => $this->makeStudent('A')->id, 'status' => 'registered']);
        AttendanceRecord::create(['registration_id' => $reg->id, 'lesson_id' => $lesson->id, 'is_present' => true]);

        Livewire::test(CourseTermGraduates::class, ['record' => $term->getRouteKey()])
            ->callTableAction('issue_certificate', $reg->id, ['lecturer_approved' => true])
            ->assertHasNoTableActionErrors();

        $this->assertSame(1, Certificate::where('registration_id', $reg->id)->count());
    }

    // --- Statistics --------------------------------------------------------------------

    public function test_statistics_page_renders(): void
    {
        $this->get('/admin/statistics')
            ->assertOk()
            ->assertSee('Přehledy a statistiky')
            ->assertSee('Odučené hodiny lektorů ve zvoleném období')
            ->assertSee('Studenti přihlášení v daném roce na více než jeden kurz');
    }

    public function test_lecturer_hours_respect_selected_period(): void
    {
        $term = $this->makeTerm(5);
        $lecturer = Lecturer::create(['first_name' => 'Jana', 'last_name' => 'Hodinová', 'email' => 'jana@example.test']);
        Lesson::create(['course_term_id' => $term->id, 'lecturer_id' => $lecturer->id, 'starts_at' => '2026-03-10 09:00', 'ends_at' => '2026-03-10 15:00', 'duration_hours' => 6, 'topic' => 'Březen']);
        Lesson::create(['course_term_id' => $term->id, 'lecturer_id' => $lecturer->id, 'starts_at' => '2026-09-10 09:00', 'ends_at' => '2026-09-10 13:00', 'duration_hours' => 4, 'topic' => 'Září']);

        Livewire::test(LecturerTaughtHoursTable::class)
            ->filterTable('period', ['from' => '2026-01-01', 'to' => '2026-06-30'])
            ->assertSee('Jana Hodinová')
            ->assertSee('6 h')
            ->assertDontSee('10 h');

        Livewire::test(LecturerTaughtHoursTable::class)
            ->filterTable('period', ['from' => '2026-01-01', 'to' => '2026-12-31'])
            ->assertSee('10 h');
    }

    public function test_multi_course_students_are_listed_for_year(): void
    {
        $otherCourse = Course::create([
            'code' => 'INT-02', 'name' => 'Druhý kurz', 'annotation' => 'Test',
            'duration_hours' => 8, 'price' => 1000, 'is_accredited' => false, 'status' => 'active',
        ]);
        $multi = $this->makeStudent('Multi');
        $single = $this->makeStudent('Single');
        $service = app(RegistrationService::class);
        $service->registerStudent($this->makeTerm(5), $multi);
        $service->registerStudent($this->makeTerm(5, $otherCourse), $multi);
        $service->registerStudent($this->makeTerm(5), $single);

        Livewire::test(MultiCourseStudentsTable::class)
            ->filterTable('year', (string) now()->year)
            ->assertSee('Číslo Multi')
            ->assertSee('Druhý kurz')
            ->assertDontSee('Číslo Single');
    }
}
