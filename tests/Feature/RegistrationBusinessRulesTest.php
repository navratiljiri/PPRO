<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Services\RegistrationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegistrationBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected RegistrationService $registrationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registrationService = app(RegistrationService::class);
    }

    private function createCourse(string $code = 'TST-01'): Course
    {
        return Course::create([
            'code' => $code,
            'name' => 'Testovací kurz ' . $code,
            'annotation' => 'Podrobná anotace testovacího kurzu.',
            'duration_hours' => 30,
            'price' => 5000.00,
            'is_accredited' => true,
            'status' => 'active',
        ]);
    }

    private function createTerm(Course $course, int $capacity = 2, ?Carbon $startDate = null): CourseTerm
    {
        return CourseTerm::create([
            'course_id' => $course->id,
            'start_date' => $startDate ?? Carbon::now()->addDays(5),
            'end_date' => ($startDate ?? Carbon::now()->addDays(5))->copy()->addDays(10),
            'classroom' => 'Učebna 101',
            'capacity' => $capacity,
            'status' => 'enrolling',
        ]);
    }

    private function createStudent(string $email, string $firstName = 'Jan', string $lastName = 'Novák'): Student
    {
        return Student::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => '+420 123 456 789',
        ]);
    }

    public function test_capacity_limit_and_fifo_waiting_list(): void
    {
        $course = $this->createCourse('CAP-01');
        $term = $this->createTerm($course, capacity: 2);

        $student1 = $this->createStudent('s1@test.cz', 'Petr', 'První');
        $student2 = $this->createStudent('s2@test.cz', 'Jana', 'Druhá');
        $student3 = $this->createStudent('s3@test.cz', 'Karel', 'Třetí');
        $student4 = $this->createStudent('s4@test.cz', 'Eva', 'Čtvrtá');

        $reg1 = $this->registrationService->registerStudent($term, $student1);
        $this->assertEquals('registered', $reg1->status);
        $this->assertNull($reg1->waiting_order);

        $reg2 = $this->registrationService->registerStudent($term, $student2);
        $this->assertEquals('registered', $reg2->status);
        $this->assertNull($reg2->waiting_order);

        // Term capacity is 2; 3rd and 4th students MUST go to FIFO waiting list
        $reg3 = $this->registrationService->registerStudent($term, $student3);
        $this->assertEquals('waiting_list', $reg3->status);
        $this->assertEquals(1, $reg3->waiting_order);

        $reg4 = $this->registrationService->registerStudent($term, $student4);
        $this->assertEquals('waiting_list', $reg4->status);
        $this->assertEquals(2, $reg4->waiting_order);
    }

    public function test_prevents_concurrent_registrations_on_same_course(): void
    {
        $course = $this->createCourse('CONC-01');
        $termA = $this->createTerm($course, capacity: 5);
        $termB = $this->createTerm($course, capacity: 5);

        $student = $this->createStudent('dupl@test.cz');

        $this->registrationService->registerStudent($termA, $student);

        // Trying to register for Term B of the same course must throw ValidationException (Rozhodnutí 3)
        $this->expectException(ValidationException::class);
        $this->registrationService->registerStudent($termB, $student);
    }

    public function test_late_registration_restrictions(): void
    {
        $course = $this->createCourse('LATE-01');
        $term = $this->createTerm($course, capacity: 5, startDate: Carbon::now()->subDays(2));

        $student = $this->createStudent('late@test.cz');

        // Without admin override -> fails
        try {
            $this->registrationService->registerStudent($term, $student, isAdminOverride: false);
            $this->fail('Should have thrown ValidationException for registration after start');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['course_term_id']));
        }

        // With admin override: if > 20% lessons elapsed -> fails
        // Let's create a lecturer and 5 lessons: 2 past, 3 future (2/5 = 40% > 20%)
        $lecturer = \App\Models\Lecturer::create([
            'first_name' => 'Lektor',
            'last_name' => 'Test',
            'email' => 'lektor@test.cz',
        ]);

        Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'starts_at' => Carbon::now()->subDays(2),
            'ends_at' => Carbon::now()->subDays(2)->addHours(4),
            'duration_hours' => 4,
            'topic' => 'Lekce 1 (proběhlá)',
        ]);
        Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'starts_at' => Carbon::now()->subDays(1),
            'ends_at' => Carbon::now()->subDays(1)->addHours(4),
            'duration_hours' => 4,
            'topic' => 'Lekce 2 (proběhlá)',
        ]);
        for ($i = 1; $i <= 3; $i++) {
            Lesson::create([
                'course_term_id' => $term->id,
                'lecturer_id' => $lecturer->id,
                'starts_at' => Carbon::now()->addDays($i),
                'ends_at' => Carbon::now()->addDays($i)->addHours(4),
                'duration_hours' => 4,
                'topic' => "Lekce future {$i}",
            ]);
        }

        try {
            $this->registrationService->registerStudent($term, $student, isAdminOverride: true);
            $this->fail('Should have blocked admin registration when > 20% elapsed');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['course_term_id']));
        }
    }

    public function test_cancellation_preserves_audit_trail_and_promotes_waiting_list(): void
    {
        $course = $this->createCourse('CANCEL-01');
        $term = $this->createTerm($course, capacity: 1);

        $student1 = $this->createStudent('reg1@test.cz', 'Aktivní', 'Student');
        $student2 = $this->createStudent('wait1@test.cz', 'Čekající', 'Náhradník');

        $reg1 = $this->registrationService->registerStudent($term, $student1);
        $reg2 = $this->registrationService->registerStudent($term, $student2);

        $this->assertEquals('registered', $reg1->status);
        $this->assertEquals('waiting_list', $reg2->status);

        // Cancel reg1
        $this->registrationService->cancelRegistration($reg1, 'Zrušeno ze zdravotních důvodů');

        // Audit Trail: record is NOT deleted, status updated to cancelled
        $this->assertDatabaseHas('registrations', [
            'id' => $reg1->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Zrušeno ze zdravotních důvodů',
        ]);

        // Promotion: reg2 must now be in 'offered' status with 24h deadline (Rozhodnutí 2)
        $reg2->refresh();
        $this->assertEquals('offered', $reg2->status);
        $this->assertNotNull($reg2->offer_expires_at);
        $this->assertTrue(Carbon::parse($reg2->offer_expires_at)->isFuture());
    }

    public function test_accept_and_decline_offer(): void
    {
        $course = $this->createCourse('OFFER-01');
        $term = $this->createTerm($course, capacity: 1);

        $student1 = $this->createStudent('off1@test.cz');
        $student2 = $this->createStudent('off2@test.cz');
        $student3 = $this->createStudent('off3@test.cz');

        $reg1 = $this->registrationService->registerStudent($term, $student1);
        $reg2 = $this->registrationService->registerStudent($term, $student2);
        $reg3 = $this->registrationService->registerStudent($term, $student3);

        // Cancel reg1 -> reg2 gets offered
        $this->registrationService->cancelRegistration($reg1, 'Odhlášení');
        $reg2->refresh();
        $this->assertEquals('offered', $reg2->status);

        // reg2 declines offer -> reg2 is cancelled, reg3 gets offered
        $this->registrationService->declineOffer($reg2, 'Nemohu se zúčastnit');
        $reg2->refresh();
        $this->assertEquals('cancelled', $reg2->status);

        $reg3->refresh();
        $this->assertEquals('offered', $reg3->status);

        // reg3 accepts offer -> reg3 becomes registered
        $this->registrationService->acceptOffer($reg3);
        $reg3->refresh();
        $this->assertEquals('registered', $reg3->status);
        $this->assertNull($reg3->offer_expires_at);
    }
}
