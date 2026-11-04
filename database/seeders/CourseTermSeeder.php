<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Services\CertificateService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Additional synthetic course terms for demonstration purposes.
 *
 * Covers every term state (enrolling, in_progress, completed, cancelled), full terms with
 * a FIFO waiting list, alternating lecturers, attendance below / above 70 %, issued
 * certificates and students enrolled in more than one course in the current year.
 * All names and contacts are fictional.
 */
class CourseTermSeeder extends Seeder
{
    /** @var array<string, Lecturer> */
    private array $lecturers = [];

    /** @var array<int, Student> */
    private array $students = [];

    public function run(): void
    {
        $this->seedLecturers();
        $this->seedStudents();

        foreach ($this->terms() as $definition) {
            $this->seedTerm($definition);
        }
    }

    private function seedLecturers(): void
    {
        $data = [
            'svoboda' => ['svoboda@akademietrutnov.cz', 'Petr', 'Svoboda', 'Účetnictví a daňová evidence'],
            'novotna' => ['novotna@akademietrutnov.cz', 'Jana', 'Novotná', 'Právo a kancelářská administrativa'],
            'dvorak' => ['dvorak@akademietrutnov.cz', 'Martin', 'Dvořák', 'Webové technologie a MS Office'],
            'mala' => ['mala@akademietrutnov.cz', 'Kateřina', 'Malá', 'Leadership, komunikace a soft skills'],
            'benes' => ['benes@akademietrutnov.cz', 'Ondřej', 'Beneš', 'Datová analýza a MS Excel'],
        ];

        foreach ($data as $key => [$email, $first, $last, $specialization]) {
            $this->lecturers[$key] = Lecturer::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'phone' => '+420 777 ' . str_pad((string) (100 + count($this->lecturers)), 3, '0') . ' 000',
                    'specialization' => $specialization,
                    'bio' => "Externí lektor/ka Akademie Trutnov – {$specialization}.",
                ]
            );
        }
    }

    private function seedStudents(): void
    {
        $names = [
            ['Adéla', 'Králová'], ['Filip', 'Beneš'], ['Karolína', 'Marková'], ['Ondřej', 'Kolář'],
            ['Tereza', 'Urbanová'], ['Jakub', 'Šimek'], ['Veronika', 'Krejčí'], ['Lukáš', 'Hájek'],
            ['Barbora', 'Novotná'], ['Matěj', 'Růžička'], ['Kristýna', 'Fialová'], ['Vojtěch', 'Sedláček'],
            ['Eliška', 'Doležalová'], ['Dominik', 'Zeman'], ['Natálie', 'Kolářová'], ['Šimon', 'Navrátil'],
            ['Klára', 'Čermáková'], ['Radek', 'Vaněk'],
        ];

        foreach ($names as $i => [$first, $last]) {
            $slug = strtolower($this->ascii($first) . '.' . $this->ascii($last));
            $this->students[$i] = Student::firstOrCreate(
                ['email' => "{$slug}@example.cz"],
                ['first_name' => $first, 'last_name' => $last, 'phone' => '+420 603 ' . sprintf('%03d', 100 + $i) . ' ' . sprintf('%03d', 200 + $i)]
            );
        }
    }

    /**
     * Term definitions. Day offsets are relative to today, so the data always looks current.
     *
     * lessons:      [lecturer key, day offset from term start, hours, topic]
     * students:     indexes into $this->students (registered in this order)
     * waiting:      indexes of students placed on the FIFO waiting list
     * absent:       student index => number of past lessons missed
     * certificates: issue certificates to eligible students (completed terms)
     *
     * @return array<int, array<string, mixed>>
     */
    private function terms(): array
    {
        return [
            // --- Completed terms (graduates, certificates, attendance below 70 %) ---
            [
                'course' => 'REK-UCET-01', 'classroom' => 'Učebna A101 (Ekonomická)', 'start' => -75, 'days' => 20,
                'capacity' => 8, 'status' => 'completed', 'note' => 'Jarní běh rekvalifikace – ukončeno závěrečnou zkouškou.',
                'lessons' => [
                    ['svoboda', 0, 6, 'Účetní soustava a účtová osnova'],
                    ['svoboda', 5, 6, 'Účtování zásob a dlouhodobého majetku'],
                    ['novotna', 10, 6, 'Pracovní právo a mzdové účetnictví'],
                    ['svoboda', 15, 6, 'DPH a daň z příjmů v praxi'],
                    ['svoboda', 20, 4, 'Účetní závěrka a závěrečný test'],
                ],
                'students' => [0, 1, 2, 3, 4, 5, 6], 'waiting' => [],
                'absent' => [3 => 2, 6 => 3], 'certificates' => true,
            ],
            [
                'course' => 'FIR-EXCEL-04', 'classroom' => 'Učebna B204 (IT Lab)', 'start' => -40, 'days' => 2,
                'capacity' => 10, 'status' => 'completed', 'note' => 'Firemní kurz pro Krkonošské strojírny (synteticky).',
                'lessons' => [
                    ['benes', 0, 8, 'Kontingenční tabulky a Power Query'],
                    ['benes', 1, 8, 'Makra a automatizace ve VBA'],
                ],
                'students' => [0, 7, 8, 9, 10], 'waiting' => [],
                'absent' => [10 => 1], 'certificates' => true,
            ],

            // --- Running terms (attendance partially recorded) ---
            [
                'course' => 'REK-PROG-03', 'classroom' => 'Učebna B205 (IT Lab 2)', 'start' => -10, 'days' => 30,
                'capacity' => 6, 'status' => 'in_progress', 'note' => 'Probíhá – výuka každé úterý a čtvrtek.',
                'lessons' => [
                    ['dvorak', 0, 6, 'HTML a sémantika webu'],
                    ['dvorak', 4, 6, 'CSS a responzivní design'],
                    ['dvorak', 8, 6, 'JavaScript – základy'],
                    ['dvorak', 15, 6, 'PHP a práce s databází'],
                    ['dvorak', 22, 6, 'Framework Laravel – úvod'],
                    ['dvorak', 30, 6, 'Závěrečný projekt a obhajoba'],
                ],
                'students' => [1, 11, 12, 13, 14, 15], 'waiting' => [16, 17],
                'absent' => [13 => 1], 'certificates' => false,
            ],
            [
                'course' => 'FIR-LEAD-06', 'classroom' => 'Školicí sál C1', 'start' => -3, 'days' => 14,
                'capacity' => 12, 'status' => 'in_progress', 'note' => 'Střídání dvou lektorů (Rozhodnutí 5).',
                'lessons' => [
                    ['mala', 0, 4, 'Role vedoucího a styly vedení'],
                    ['novotna', 4, 4, 'Pracovněprávní minimum pro mistry'],
                    ['mala', 9, 4, 'Zpětná vazba a motivace týmu'],
                    ['mala', 14, 4, 'Řešení konfliktů – modelové situace'],
                ],
                'students' => [2, 4, 7, 9, 16], 'waiting' => [],
                'absent' => [], 'certificates' => false,
            ],

            // --- Enrolling terms (one full with waiting list, others partially filled) ---
            [
                'course' => 'REK-ASIS-02', 'classroom' => 'Učebna A103', 'start' => 12, 'days' => 21,
                'capacity' => 5, 'status' => 'enrolling', 'note' => 'Plně obsazeno – zájemci jsou zařazováni do pořadníku.',
                'lessons' => [
                    ['novotna', 0, 6, 'Kancelářská komunikace a etiketa'],
                    ['novotna', 7, 6, 'Spisová služba a archivace'],
                    ['dvorak', 14, 6, 'MS Word a Outlook pro asistentky'],
                    ['novotna', 21, 6, 'Závěrečná zkouška'],
                ],
                'students' => [3, 5, 8, 10, 12], 'waiting' => [14, 17, 6],
                'absent' => [], 'certificates' => false,
            ],
            [
                'course' => 'FIR-TIME-05', 'classroom' => 'Školicí sál C1', 'start' => 20, 'days' => 1,
                'capacity' => 15, 'status' => 'enrolling', 'note' => 'Jednodenní workshop.',
                'lessons' => [
                    ['mala', 0, 4, 'Plánování a prioritizace (Eisenhower)'],
                    ['benes', 1, 4, 'Digitální nástroje produktivity'],
                ],
                'students' => [0, 11, 15], 'waiting' => [],
                'absent' => [], 'certificates' => false,
            ],
            [
                'course' => 'FIR-EXCEL-04', 'classroom' => 'Učebna B204 (IT Lab)', 'start' => 30, 'days' => 2,
                'capacity' => 10, 'status' => 'enrolling', 'note' => 'Otevřený termín pro veřejnost.',
                'lessons' => [
                    ['benes', 0, 8, 'Pokročilé vzorce a kontingenční tabulky'],
                    ['benes', 1, 8, 'Makra a VBA'],
                ],
                'students' => [13, 17], 'waiting' => [],
                'absent' => [], 'certificates' => false,
            ],
            [
                'course' => 'REK-UCET-01', 'classroom' => 'Učebna A104 (Ekonomická 2)', 'start' => 45, 'days' => 20,
                'capacity' => 10, 'status' => 'enrolling', 'note' => 'Podzimní běh rekvalifikace.',
                'lessons' => [
                    ['svoboda', 0, 6, 'Účetní soustava a účtová osnova'],
                    ['novotna', 10, 6, 'Pracovní právo a mzdy'],
                    ['svoboda', 20, 6, 'Účetní závěrka'],
                ],
                'students' => [9, 16], 'waiting' => [],
                'absent' => [], 'certificates' => false,
            ],

            // --- Cancelled term (registrations kept for audit) ---
            [
                'course' => 'FIR-LEAD-06', 'classroom' => 'Hotel Horal (externí)', 'start' => 25, 'days' => 2,
                'capacity' => 12, 'status' => 'cancelled', 'note' => 'Zrušeno pro nízký počet přihlášených.',
                'lessons' => [],
                'students' => [], 'waiting' => [],
                'cancelled' => [1, 5],
                'absent' => [], 'certificates' => false,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     */
    private function seedTerm(array $d): void
    {
        $course = Course::where('code', $d['course'])->first();
        if (! $course) {
            return;
        }

        $start = Carbon::today()->addDays($d['start'])->setTime(9, 0);

        $term = CourseTerm::firstOrCreate(
            ['course_id' => $course->id, 'classroom' => $d['classroom'], 'start_date' => $start],
            [
                'end_date' => $start->copy()->addDays($d['days'])->setTime(16, 0),
                'capacity' => $d['capacity'],
                'status' => $d['status'],
                'note' => $d['note'],
            ]
        );

        if (! $term->wasRecentlyCreated) {
            return; // idempotent – do not duplicate registrations on re-seed
        }

        $lessons = collect($d['lessons'])->map(fn (array $l) => Lesson::create([
            'course_term_id' => $term->id,
            'lecturer_id' => $this->lecturers[$l[0]]->id,
            'starts_at' => $start->copy()->addDays($l[1]),
            'ends_at' => $start->copy()->addDays($l[1])->addHours($l[2]),
            'duration_hours' => $l[2],
            'topic' => $l[3],
        ]));

        $registeredAt = $start->copy()->subDays(30);
        $isFinished = $d['status'] === 'completed';

        foreach ($d['students'] as $order => $studentIndex) {
            $registration = Registration::create([
                'course_term_id' => $term->id,
                'student_id' => $this->students[$studentIndex]->id,
                'status' => 'registered',
            ]);
            $registration->forceFill(['created_at' => $registeredAt->copy()->addHours($order)])->saveQuietly();

            // Attendance for lessons that already took place
            $pastLessons = $lessons->filter(fn (Lesson $lesson) => $lesson->starts_at->isPast())->values();
            $missed = $d['absent'][$studentIndex] ?? 0;
            foreach ($pastLessons as $i => $lesson) {
                AttendanceRecord::create([
                    'registration_id' => $registration->id,
                    'lesson_id' => $lesson->id,
                    'is_present' => $i >= $missed,
                    'note' => $i < $missed ? 'Omluvená absence' : null,
                ]);
            }

            // Completed term: issue certificate only to students meeting the 70 % rule (Rozhodnutí 1)
            if ($isFinished && $d['certificates'] && $this->meetsThreshold($pastLessons->count(), $missed)) {
                app(CertificateService::class)->issueCertificate($registration);
            }
        }

        foreach ($d['waiting'] as $position => $studentIndex) {
            $registration = Registration::create([
                'course_term_id' => $term->id,
                'student_id' => $this->students[$studentIndex]->id,
                'status' => 'waiting_list',
                'waiting_order' => $position + 1,
            ]);
            $registration->forceFill(['created_at' => $registeredAt->copy()->addDays(5 + $position)])->saveQuietly();
        }

        foreach ($d['cancelled'] ?? [] as $studentIndex) {
            Registration::create([
                'course_term_id' => $term->id,
                'student_id' => $this->students[$studentIndex]->id,
                'status' => 'cancelled',
                'cancellation_reason' => 'Termín zrušen pořadatelem (nízký počet přihlášených).',
            ]);
        }
    }

    private function meetsThreshold(int $total, int $missed): bool
    {
        return $total > 0 && (($total - $missed) / $total) >= 0.7;
    }

    private function ascii(string $value): string
    {
        return Str::ascii($value);
    }
}
