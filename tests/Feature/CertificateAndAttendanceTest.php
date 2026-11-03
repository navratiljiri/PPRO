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
use App\Services\AttendanceService;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CertificateAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $attendanceService;
    protected CertificateService $certificateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendanceService = app(AttendanceService::class);
        $this->certificateService = app(CertificateService::class);
    }

    public function test_attendance_calculation_and_70_percent_threshold(): void
    {
        $course = Course::create([
            'code' => 'ATT-01',
            'name' => 'Docházkový kurz',
            'annotation' => 'Test docházky',
            'duration_hours' => 20,
            'price' => 3000,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $term = CourseTerm::create([
            'course_id' => $course->id,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(1),
            'classroom' => 'Učebna 1',
            'capacity' => 10,
            'status' => 'completed',
        ]);

        $lecturer = Lecturer::create([
            'first_name' => 'Test',
            'last_name' => 'Lektor',
            'email' => 'lektor.dochazka@test.cz',
        ]);

        // Create 4 lessons
        $lessons = [];
        for ($i = 1; $i <= 4; $i++) {
            $lessons[] = Lesson::create([
                'course_term_id' => $term->id,
                'lecturer_id' => $lecturer->id,
                'starts_at' => Carbon::now()->subDays(10 - $i),
                'ends_at' => Carbon::now()->subDays(10 - $i)->addHours(4),
                'duration_hours' => 4,
                'topic' => "Lekce {$i}",
            ]);
        }

        $student = Student::create([
            'first_name' => 'Karel',
            'last_name' => 'Účastník',
            'email' => 'karel.ucastnik@test.cz',
        ]);

        $reg = Registration::create([
            'course_term_id' => $term->id,
            'student_id' => $student->id,
            'status' => 'registered',
        ]);

        // Case 1: Present in 2 out of 4 lessons = 50% -> fails threshold
        AttendanceRecord::create(['registration_id' => $reg->id, 'lesson_id' => $lessons[0]->id, 'is_present' => true]);
        AttendanceRecord::create(['registration_id' => $reg->id, 'lesson_id' => $lessons[1]->id, 'is_present' => true]);
        AttendanceRecord::create(['registration_id' => $reg->id, 'lesson_id' => $lessons[2]->id, 'is_present' => false]);
        AttendanceRecord::create(['registration_id' => $reg->id, 'lesson_id' => $lessons[3]->id, 'is_present' => false]);

        $stats = $this->attendanceService->calculateAttendanceStats($reg);
        $this->assertEquals(50.0, $stats['percentage']);
        $this->assertFalse($stats['meets_threshold']);

        // Issuing certificate must fail without admin exception
        try {
            $this->certificateService->issueCertificate($reg, lecturerApproved: true, adminException: false);
            $this->fail('Should have failed because 50% < 70% threshold');
        } catch (ValidationException $e) {
            $this->assertTrue(isset($e->errors()['attendance']));
        }

        // Case 2: Present in 3rd lesson as well -> 3 out of 4 = 75% -> meets 70% threshold!
        AttendanceRecord::where('registration_id', $reg->id)->where('lesson_id', $lessons[2]->id)->update(['is_present' => true]);

        $statsUpdated = $this->attendanceService->calculateAttendanceStats($reg);
        $this->assertEquals(75.0, $statsUpdated['percentage']);
        $this->assertTrue($statsUpdated['meets_threshold']);

        // Issuing certificate now succeeds
        $cert = $this->certificateService->issueCertificate($reg, lecturerApproved: true);
        $this->assertNotNull($cert);
        $this->assertStringStartsWith('AT-' . Carbon::now()->year . '-', $cert->certificate_number);

        // Registration status is completed
        $reg->refresh();
        $this->assertEquals('completed', $reg->status);
    }

    public function test_certificate_immutability(): void
    {
        $course = Course::create([
            'code' => 'IMM-01',
            'name' => 'Neměnný kurz',
            'annotation' => 'Test neměnnosti osvědčení',
            'duration_hours' => 10,
            'price' => 2000,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $term = CourseTerm::create([
            'course_id' => $course->id,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->subDays(1),
            'classroom' => 'Učebna 2',
            'capacity' => 5,
            'status' => 'completed',
        ]);

        $student = Student::create([
            'first_name' => 'Petr',
            'last_name' => 'Certifikovaný',
            'email' => 'cert@test.cz',
        ]);

        $reg = Registration::create([
            'course_term_id' => $term->id,
            'student_id' => $student->id,
            'status' => 'completed',
        ]);

        $certificate = Certificate::create([
            'registration_id' => $reg->id,
            'certificate_number' => 'AT-2026-99999',
            'issue_date' => Carbon::now()->toDateString(),
            'lecturer_approved' => true,
        ]);

        // Attempting to update the certificate record must throw RuntimeException (Obchodní pravidlo neměnnosti)
        $this->expectException(\RuntimeException::class);
        $certificate->update(['certificate_number' => 'AT-2026-00000']);
    }
}
