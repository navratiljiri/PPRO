<?php

namespace Tests\Feature;

use App\Filament\Resources\Certificates\Pages\CreateCertificate;
use App\Models\AttendanceRecord;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Certificate issuing form in the administration – goes through CertificateService,
 * so the number is generated automatically and the 70 % attendance rule is enforced.
 */
class FilamentCertificateCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Správce Akademie',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
        ]));
    }

    private function makeRegistration(int $presentLessons, int $totalLessons = 4): Registration
    {
        $course = Course::create([
            'code' => 'CRT-01',
            'name' => 'Kurz pro osvědčení',
            'annotation' => 'Test',
            'duration_hours' => 16,
            'price' => 2000,
            'is_accredited' => true,
            'status' => 'active',
        ]);

        $term = CourseTerm::create([
            'course_id' => $course->id,
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDay(),
            'classroom' => 'Učebna 2',
            'capacity' => 10,
            'status' => 'completed',
        ]);

        $lecturer = Lecturer::create(['first_name' => 'Eva', 'last_name' => 'Lektorová', 'email' => 'eva@example.test']);
        $student = Student::create(['first_name' => 'Jan', 'last_name' => 'Absolvent', 'email' => 'jan@example.test']);

        $registration = Registration::create([
            'course_term_id' => $term->id,
            'student_id' => $student->id,
            'status' => 'registered',
        ]);

        for ($i = 0; $i < $totalLessons; $i++) {
            $lesson = Lesson::create([
                'course_term_id' => $term->id,
                'lecturer_id' => $lecturer->id,
                'starts_at' => Carbon::now()->subDays(9 - $i),
                'ends_at' => Carbon::now()->subDays(9 - $i)->addHours(4),
                'duration_hours' => 4,
                'topic' => "Lekce {$i}",
            ]);

            AttendanceRecord::create([
                'registration_id' => $registration->id,
                'lesson_id' => $lesson->id,
                'is_present' => $i < $presentLessons,
            ]);
        }

        return $registration;
    }

    public function test_create_page_renders_form(): void
    {
        $this->get('/admin/certificates/create')
            ->assertOk()
            ->assertSee('Vystavení osvědčení o absolvování')
            ->assertSee('Přihláška (absolvent a termín)');
    }

    public function test_certificate_is_issued_with_generated_number(): void
    {
        $registration = $this->makeRegistration(presentLessons: 4);

        Livewire::test(CreateCertificate::class)
            ->fillForm([
                'registration_id' => $registration->id,
                'lecturer_approved' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $certificate = Certificate::firstOrFail();
        $this->assertSame($registration->id, $certificate->registration_id);
        $this->assertMatchesRegularExpression('/^AT-\d{4}-\d{5}$/', $certificate->certificate_number);
        $this->assertSame('completed', $registration->fresh()->status);
    }

    public function test_insufficient_attendance_is_rejected_without_exception(): void
    {
        $registration = $this->makeRegistration(presentLessons: 2);

        Livewire::test(CreateCertificate::class)
            ->fillForm([
                'registration_id' => $registration->id,
                'lecturer_approved' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['registration_id']);

        $this->assertSame(0, Certificate::count());
    }

    public function test_admin_exception_requires_reason_and_allows_issuing(): void
    {
        $registration = $this->makeRegistration(presentLessons: 2);

        Livewire::test(CreateCertificate::class)
            ->fillForm([
                'registration_id' => $registration->id,
                'lecturer_approved' => true,
                'admin_exception' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['exception_reason' => 'required']);

        Livewire::test(CreateCertificate::class)
            ->fillForm([
                'registration_id' => $registration->id,
                'lecturer_approved' => true,
                'admin_exception' => true,
                'exception_reason' => 'Náhradní seminární práce',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(1, Certificate::count());
    }

    public function test_certificates_cannot_be_edited(): void
    {
        $registration = $this->makeRegistration(presentLessons: 4);
        $certificate = app(\App\Services\CertificateService::class)->issueCertificate($registration);

        $this->get("/admin/certificates/{$certificate->id}/edit")->assertNotFound();
    }
}
