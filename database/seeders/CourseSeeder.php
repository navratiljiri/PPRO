<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'code' => 'REK-UCET-01',
                'name' => 'Účetnictví a daňová evidence pro praxi',
                'annotation' => 'Komplexní rekvalifikační kurz akreditovaný MŠMT ČR. Pokrývá podvojné účetnictví, daňovou evidenci, mzdy a odvody, roční účetní závěrku a práci v moderních účetních systémech. Vhodné pro začátečníky i pro návrat na trh práce.',
                'duration_hours' => 120,
                'price' => 16500.00,
                'is_accredited' => true,
                'status' => 'active',
            ],
            [
                'code' => 'REK-ASIS-02',
                'name' => 'Asistent/ka a administrativní pracovník',
                'annotation' => 'Akreditovaný program zaměřený na moderní kancelářskou praxi, obchodní korespondenci, základy firemního práva, efektivní archivaci dokumentů a digitální nástroje pro týmovou spolupráci.',
                'duration_hours' => 80,
                'price' => 12900.00,
                'is_accredited' => true,
                'status' => 'active',
            ],
            [
                'code' => 'REK-PROG-03',
                'name' => 'Programování a základy webových technologií',
                'annotation' => 'Intenzivní rekvalifikační kurz zaměřený na základy algoritmizace, HTML, CSS, JavaScript a základy backendu. Kurz je akreditován v rámci dotačních programů MPSV „Jsem v kurzu“.',
                'duration_hours' => 100,
                'price' => 19800.00,
                'is_accredited' => true,
                'status' => 'active',
            ],
            [
                'code' => 'FIR-EXCEL-04',
                'name' => 'Pokročilá analýza dat a makra v MS Excel',
                'annotation' => 'Praktický firemní workshop zaměřený na kontingenční tabulky, pokročilé vzorce (XLOOKUP, INDEX/MATCH), Power Query a automatizaci rutinních úloh pomocí maker pro firemní analytiky a manažery.',
                'duration_hours' => 24,
                'price' => 7800.00,
                'is_accredited' => false,
                'status' => 'active',
            ],
            [
                'code' => 'FIR-TIME-05',
                'name' => 'Time management a digitální produktivita',
                'annotation' => 'Intenzivní dvoudenní kurz technik řízení priorit, zvládání stresu, organizace pracovního dne a využívání moderních nástrojů pro eliminaci prokrastinace a efektivní delegování úkolů.',
                'duration_hours' => 16,
                'price' => 6200.00,
                'is_accredited' => false,
                'status' => 'active',
            ],
            [
                'code' => 'FIR-LEAD-06',
                'name' => 'Efektivní leadership a vedení lidí pro mistry a vedoucí',
                'annotation' => 'Rozvojový kurz pro vedoucí pracovníky v průmyslu i službách. Praktický nácvik vedení hodnotících rozhovorů, řešení konfliktů na pracovišti, motivace zaměstnanců a asertivní komunikace.',
                'duration_hours' => 32,
                'price' => 9500.00,
                'is_accredited' => false,
                'status' => 'active',
            ],
        ];

        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['code' => $courseData['code']],
                $courseData
            );
        }
    }
}
