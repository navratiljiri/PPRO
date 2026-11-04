<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete synthetic data.
     */
    public function run(): void
    {
        // 1. Admin user for Filament administration
        User::firstOrCreate(
            ['email' => 'admin@akademietrutnov.cz'],
            [
                'name' => 'Správce Akademie Trutnov',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Courses
        $this->call(CourseSeeder::class);
        $courseUcet = Course::where('code', 'REK-UCET-01')->first();
        $courseAsis = Course::where('code', 'REK-ASIS-02')->first();
        $courseProg = Course::where('code', 'REK-PROG-03')->first();
        $courseExcel = Course::where('code', 'FIR-EXCEL-04')->first();

        // 3. Lecturers (Externists)
        $lecturer1 = Lecturer::firstOrCreate(
            ['email' => 'svoboda@akademietrutnov.cz'],
            [
                'first_name' => 'Petr',
                'last_name' => 'Svoboda',
                'phone' => '+420 777 123 456',
                'specialization' => 'Účetnictví a daňová evidence',
                'bio' => 'Dlouholetý daňový poradce a certifikovaný účetní expert pro Královéhradecký kraj.',
            ]
        );

        $lecturer2 = Lecturer::firstOrCreate(
            ['email' => 'novotna@akademietrutnov.cz'],
            [
                'first_name' => 'Jana',
                'last_name' => 'Novotná',
                'phone' => '+420 777 234 567',
                'specialization' => 'Právo a kancelářská administrativa',
                'bio' => 'Právnička se zaměřením na pracovní právo a moderní digitální spisovou službu.',
            ]
        );

        $lecturer3 = Lecturer::firstOrCreate(
            ['email' => 'dvorak@akademietrutnov.cz'],
            [
                'first_name' => 'Martin',
                'last_name' => 'Dvořák',
                'phone' => '+420 777 345 678',
                'specialization' => 'Webové technologie a MS Office',
                'bio' => 'Senior softwarový inženýr a lektor rekvalifikačních IT programů MPSV.',
            ]
        );

        // 4. Course Terms
        // Term 1: Capacity 3 (for demonstrating capacity limits & FIFO waiting list)
        $term1 = CourseTerm::firstOrCreate(
            [
                'course_id' => $courseUcet->id,
                'classroom' => 'Učebna A101 (Ekonomická)',
            ],
            [
                'start_date' => Carbon::now()->addDays(7)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(21)->setTime(16, 0),
                'capacity' => 3,
                'status' => 'enrolling',
                'note' => 'Vybaveno PC pro každého účastníka s licencí účetního software.',
            ]
        );

        // Term 2: Programming
        $term2 = CourseTerm::firstOrCreate(
            [
                'course_id' => $courseProg->id,
                'classroom' => 'Učebna B204 (IT Lab)',
            ],
            [
                'start_date' => Carbon::now()->addDays(14)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(35)->setTime(15, 30),
                'capacity' => 6,
                'status' => 'enrolling',
                'note' => 'Dotační program MPSV Jsem v kurzu.',
            ]
        );

        // Term 3: Completed Term (for certificate verification)
        $termCompleted = CourseTerm::firstOrCreate(
            [
                'course_id' => $courseAsis->id,
                'classroom' => 'Učebna A102',
            ],
            [
                'start_date' => Carbon::now()->subDays(30)->setTime(9, 0),
                'end_date' => Carbon::now()->subDays(5)->setTime(16, 0),
                'capacity' => 4,
                'status' => 'completed',
                'note' => 'Termín úspěšně ukončen závěrečnou zkouškou.',
            ]
        );

        // 5. Lessons with alternating lecturers (Rozhodnutí 5)
        // Term 1: Lesson 1 (Svoboda), Lesson 2 (Novotná - legal aspects), Lesson 3 (Svoboda)
        $lesson1 = Lesson::firstOrCreate(
            [
                'course_term_id' => $term1->id,
                'topic' => 'Modul 1: Základy podvojného účetnictví a účtová osnova',
            ],
            [
                'lecturer_id' => $lecturer1->id,
                'starts_at' => Carbon::now()->addDays(7)->setTime(9, 0),
                'ends_at' => Carbon::now()->addDays(7)->setTime(15, 0),
                'duration_hours' => 6,
            ]
        );

        $lesson2 = Lesson::firstOrCreate(
            [
                'course_term_id' => $term1->id,
                'topic' => 'Modul 2: Právní a daňové minimum pro účetní praxi (Zákoník práce)',
            ],
            [
                'lecturer_id' => $lecturer2->id, // Alternating lecturer!
                'starts_at' => Carbon::now()->addDays(14)->setTime(9, 0),
                'ends_at' => Carbon::now()->addDays(14)->setTime(15, 0),
                'duration_hours' => 6,
            ]
        );

        $lesson3 = Lesson::firstOrCreate(
            [
                'course_term_id' => $term1->id,
                'topic' => 'Modul 3: Daňové přiznání a účetní závěrka v praxi',
            ],
            [
                'lecturer_id' => $lecturer1->id,
                'starts_at' => Carbon::now()->addDays(21)->setTime(9, 0),
                'ends_at' => Carbon::now()->addDays(21)->setTime(15, 0),
                'duration_hours' => 6,
            ]
        );

        // Term Completed lessons
        $compLesson1 = Lesson::firstOrCreate(
            [
                'course_term_id' => $termCompleted->id,
                'topic' => 'Blok 1: Spisová služba a elektronická komunikace',
            ],
            [
                'lecturer_id' => $lecturer2->id,
                'starts_at' => Carbon::now()->subDays(25)->setTime(9, 0),
                'ends_at' => Carbon::now()->subDays(25)->setTime(16, 0),
                'duration_hours' => 8,
            ]
        );

        $compLesson2 = Lesson::firstOrCreate(
            [
                'course_term_id' => $termCompleted->id,
                'topic' => 'Blok 2: Závěrečná certifikační zkouška a obhajoba',
            ],
            [
                'lecturer_id' => $lecturer2->id,
                'starts_at' => Carbon::now()->subDays(10)->setTime(9, 0),
                'ends_at' => Carbon::now()->subDays(10)->setTime(16, 0),
                'duration_hours' => 8,
            ]
        );

        // 6. Students
        $student1 = Student::firstOrCreate(
            ['email' => 'tomas.kucera@email.cz'],
            ['first_name' => 'Tomáš', 'last_name' => 'Kučera', 'phone' => '+420 601 111 222']
        );
        $student2 = Student::firstOrCreate(
            ['email' => 'lucie.cerna@email.cz'],
            ['first_name' => 'Lucie', 'last_name' => 'Černá', 'phone' => '+420 601 222 333']
        );
        $student3 = Student::firstOrCreate(
            ['email' => 'jan.vesely@email.cz'],
            ['first_name' => 'Jan', 'last_name' => 'Veselý', 'phone' => '+420 601 333 444']
        );
        $student4 = Student::firstOrCreate(
            ['email' => 'michaela.horakova@email.cz'],
            ['first_name' => 'Michaela', 'last_name' => 'Horáková', 'phone' => '+420 601 444 555']
        );
        $student5 = Student::firstOrCreate(
            ['email' => 'petr.pokorny@email.cz'],
            ['first_name' => 'Petr', 'last_name' => 'Pokorný', 'phone' => '+420 601 555 666']
        );
        $studentGraduate = Student::firstOrCreate(
            ['email' => 'david.pospisil@email.cz'],
            ['first_name' => 'David', 'last_name' => 'Pospíšil', 'phone' => '+420 601 777 888']
        );

        // 7. Registrations
        // Term 1 has capacity 3:
        // First 3 students are registered
        Registration::firstOrCreate(
            ['course_term_id' => $term1->id, 'student_id' => $student1->id],
            ['status' => 'registered', 'waiting_order' => null]
        );
        Registration::firstOrCreate(
            ['course_term_id' => $term1->id, 'student_id' => $student2->id],
            ['status' => 'registered', 'waiting_order' => null]
        );
        Registration::firstOrCreate(
            ['course_term_id' => $term1->id, 'student_id' => $student3->id],
            ['status' => 'registered', 'waiting_order' => null]
        );

        // 4th and 5th students are in FIFO waiting list!
        Registration::firstOrCreate(
            ['course_term_id' => $term1->id, 'student_id' => $student4->id],
            ['status' => 'waiting_list', 'waiting_order' => 1]
        );
        Registration::firstOrCreate(
            ['course_term_id' => $term1->id, 'student_id' => $student5->id],
            ['status' => 'waiting_list', 'waiting_order' => 2]
        );

        // Student 1 also registers for Programming (multi-course student!)
        Registration::firstOrCreate(
            ['course_term_id' => $term2->id, 'student_id' => $student1->id],
            ['status' => 'registered', 'waiting_order' => null]
        );

        // Graduate registration on completed term
        $gradReg = Registration::firstOrCreate(
            ['course_term_id' => $termCompleted->id, 'student_id' => $studentGraduate->id],
            ['status' => 'completed', 'waiting_order' => null]
        );

        // Attendance records for graduate (100% presence)
        AttendanceRecord::firstOrCreate(
            ['registration_id' => $gradReg->id, 'lesson_id' => $compLesson1->id],
            ['is_present' => true, 'note' => 'Aktivní účast']
        );
        AttendanceRecord::firstOrCreate(
            ['registration_id' => $gradReg->id, 'lesson_id' => $compLesson2->id],
            ['is_present' => true, 'note' => 'Zkouška složena na výbornou']
        );

        // 8. Issued Certificate for Graduate
        Certificate::firstOrCreate(
            ['registration_id' => $gradReg->id],
            [
                'certificate_number' => 'AT-2026-00001',
                'issue_date' => Carbon::now()->subDays(5)->toDateString(),
                'lecturer_approved' => true,
            ]
        );

        // 9. Additional demo terms in all states (running, completed, cancelled, full with waiting list)
        $this->call(CourseTermSeeder::class);
    }
}
