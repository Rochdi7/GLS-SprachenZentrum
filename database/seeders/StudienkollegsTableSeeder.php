<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Studienkolleg;
use Illuminate\Support\Str;

class StudienkollegsTableSeeder extends Seeder
{
    public function run(): void
    {
        Studienkolleg::truncate();

        // Helper: convert short course codes to your labels
        $courseMap = [
            'T'  => 'T Course',
            'W'  => 'W Course',
            'M'  => 'M Course',
            'G'  => 'G Course',
            'S'  => 'S Course',
            'TI' => 'TI Course',
            'WW' => 'WW Course',
            'SW' => 'SW Course',
        ];

        $makeCourses = function (array $codes) use ($courseMap) {
            $out = [];
            foreach ($codes as $c) {
                $c = trim($c);
                if ($c === '') continue;
                $out[] = $courseMap[$c] ?? $c;
            }
            return array_values(array_unique($out));
        };

        /**
         * Helper: Convert deadlines from MAP format (start/end) to LIST format (range).
         * Intelligently handles null values:
         *   - start=null, end='30.06' => 'bis 30.06'
         *   - start='15.04', end=null => 'ab 15.04'
         *   - start=null, end=null => '' (empty range)
         */
        $convertDeadlinesToRange = function (?array $mapDeadlines): ?array {
            if (!is_array($mapDeadlines) || empty($mapDeadlines)) {
                return null;
            }

            $list = [];
            $mapping = [
                'Winter Semester' => 'Winter Semester (WS)',
                'Summer Semester' => 'Summer Semester (SS)',
            ];

            foreach ($mapping as $formKey => $semesterLabel) {
                if (isset($mapDeadlines[$formKey]) && is_array($mapDeadlines[$formKey])) {
                    $row = $mapDeadlines[$formKey];
                    $start = isset($row['start']) ? trim($row['start']) : null;
                    $end = isset($row['end']) ? trim($row['end']) : null;
                    $note = isset($row['note']) ? trim($row['note']) : '';

                    // Build range intelligently
                    $range = '';
                    if (!empty($start) && !empty($end)) {
                        $range = "$start – $end";
                    } elseif (empty($start) && !empty($end)) {
                        $range = "bis $end";
                    } elseif (!empty($start) && empty($end)) {
                        $range = "ab $start";
                    }
                    // else: both null => range stays empty

                    // Only add row if range or note exists
                    if (!empty($range) || !empty($note)) {
                        $list[] = [
                            'semester' => $semesterLabel,
                            'range' => $range,
                            'note' => $note,
                        ];
                    }
                }
            }

            return !empty($list) ? $list : null;
        };

        /**
         * Helper: Apply default values for NOT NULL fields to prevent constraint violations.
         * This ensures every required field has a safe value (never null).
         * 
         * Defaults applied:
         * - Boolean flags: translation_required=false, certification_required=true
         * - String fields: translation_note='', contact_email='', address='', map_embed='', exam_subjects='', exam_link=''
         * - Array fields: languages=[], courses=[], documents=[], deadlines=[], requirements=[]
         */
        $applyDefaults = function (array $data): array {
            return array_merge([
                // Boolean flags with safe defaults
                'translation_required' => false,
                'certification_required' => true,

                // String fields with empty/placeholder defaults
                'translation_note' => '',
                'contact_email' => '',
                'address' => '',
                'map_embed' => '',
                'exam_subjects' => '',
                'exam_link' => '',

                // Array fields with empty array defaults
                'languages' => [],
                'courses' => [],
                'documents' => [],
                'deadlines' => [],
                'requirements' => [],
            ], $data);
        };

        $seed = function (array $data) use ($applyDefaults, $convertDeadlinesToRange) {
            $data = $applyDefaults($data);
            // Convert deadlines from MAP format (start/end) to LIST format (range)
            if (!empty($data['deadlines']) && is_array($data['deadlines'])) {
                // Check if it's MAP format (has 'Winter Semester' or 'Summer Semester' keys)
                if (isset($data['deadlines']['Winter Semester']) || isset($data['deadlines']['Summer Semester'])) {
                    $data['deadlines'] = $convertDeadlinesToRange($data['deadlines']);
                }
            }
            $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
            Studienkolleg::create($data);
        };

        // ===============================
        // 1) Studienkolleg an der FH Kiel
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der FH Kiel',
            'university' => 'Fachhochschule Kiel',
            'city' => 'Kiel',
            'state' => 'Schleswig-Holstein',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/1.webp',
            'card_image' => 'assets/images/studienkollegs/1.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['TI', 'WW']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '01.11', 'end' => '31.03', 'note' => ''],
                'Summer Semester' => ['start' => '01.05', 'end' => '30.09', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1',
            ],

            'application_method' => 'Direct application (university portal)',
            'application_portal_note' => 'Bewerbungsunterlagen: see official page',
            'application_url' => 'https://casy.fh-kiel.de/qisserver/pages/cs/sys/portal/hisinoneStartPage.faces',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://www.fh-kiel.de/studium/studieninteressierte/studienkolleg/',

            'meta_title' => 'Studienkolleg FH Kiel – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at FH Kiel for international applicants. Courses, deadlines, and requirements.',
        ]);

        // ===============================
        // 2) Studienkolleg an der TU Darmstadt
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der TU Darmstadt',
            'university' => 'Technische Universität Darmstadt',
            'city' => 'Darmstadt',
            'state' => 'Hesse',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/2.webp',
            'card_image' => 'assets/images/studienkollegs/2.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['T', 'G']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '15.03', 'end' => '15.04', 'note' => ''],
                'Summer Semester' => ['start' => '15.09', 'end' => '15.10', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1',
            ],

            'application_method' => 'Direct application (university portal)',
            'application_portal_note' => 'Bewerbungsunterlagen: see official page',
            'application_url' => 'https://www.tucan.tu-darmstadt.de/scripts/mgrqispi.dll?APPNAME=CampusNet&PRGNAME=EXTERNALPAGES&ARGUMENTS=-N000000000000001,-N000344,-Awelcome',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.stk.tu-darmstadt.de/',

            'meta_title' => 'Studienkolleg TU Darmstadt – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at TU Darmstadt. Courses, deadlines and requirements for international students.',
        ]);

        // ===============================
        // 3) Studienkolleg des KIT (Karlsruher Institut für Technologie)
        // ===============================
        $seed([
            'name' => 'Studienkolleg des KIT (Karlsruher Institut für Technologie)',
            'university' => 'Karlsruher Institut für Technologie (KIT)',
            'city' => 'Karlsruhe',
            'state' => 'Baden-Württemberg',
            'country' => 'Germany',

            'featured' => true,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/3.webp',
            'card_image' => 'assets/images/studienkollegs/3.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['T']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '01.05', 'end' => '15.07', 'note' => ''],
                'Summer Semester' => ['start' => '01.11', 'end' => '15.01', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1',
                'KIT note: minimum 70% level (as provided)',
            ],

            'application_method' => 'Direct application (KIT portal)',
            'application_portal_note' => 'Bewerbungsunterlagen: see official page',
            'application_url' => 'https://bewerbung.studium.kit.edu/prod/campus/Portal/Start',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.stk.kit.edu/',

            'meta_title' => 'Studienkolleg KIT – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at KIT (Karlsruhe). Deadlines, courses and requirements.',
        ]);

        // ===============================
        // 4) Ökumenisches Studienwerk e.V. (Bochum)
        // ===============================
        $seed([
            'name' => 'Studienkolleg des Ökumenischen Studienwerks e.V.',
            'university' => 'Ökumenisches Studienwerk e.V.',
            'city' => 'Bochum',
            'state' => 'North Rhine-Westphalia',
            'country' => 'Germany',

            'featured' => false,
            'public' => false,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Unknown',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/4.webp',
            'card_image' => 'assets/images/studienkollegs/4.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['T']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '15.02', 'end' => '15.05', 'note' => ''],
                'Summer Semester' => ['start' => '15.08', 'end' => '15.10', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
            ],

            'application_method' => 'Direct application (website)',
            'application_portal_note' => null,
            'application_url' => 'https://www.studienkolleg-bochum.de/index.php/de/vorstudienkurse/zulassung-bewerbung',

            'exam_link' => 'https://www.studienkolleg-bochum.de/index.php/de/vorstudienkurse/zulassung-bewerbung',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.studienkolleg-bochum.de/',

            'meta_title' => 'Studienkolleg Bochum – Admission & Deadlines',
            'meta_description' => 'Ökumenisches Studienkolleg Bochum. Deadlines, courses and requirements.',
        ]);

        // ===============================
        // 5) Studienkolleg Coburg
        // ===============================
        $seed([
            'name' => 'Studienkolleg Coburg',
            'university' => 'Hochschule Coburg (Studienkolleg)',
            'city' => 'Coburg',
            'state' => 'Bavaria',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Unknown',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/5.webp',
            'card_image' => 'assets/images/studienkollegs/5.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['TI', 'WW']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1+/B2)',
                'Passport copy',
                'VPD (uni-assist) if required',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '15.02', 'end' => '15.07', 'note' => ''],
                'Summer Semester' => ['start' => null, 'end' => '15.01', 'note' => 'as provided: "bis 15.01"'],
            ],
            'requirements' => [
                'German level: B1+/B2',
                'Note: VPD from uni-assist (as provided)',
            ],

            'application_method' => 'Direct application (Primuss portal)',
            'application_portal_note' => null,
            'application_url' => 'https://www3.primuss.de/cgi-bin/bew_anmeldung_v2/index.pl?Session=&FH=fhc&Email=&Portal=1&Language=de',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.studienkolleg-coburg.de/',

            'meta_title' => 'Studienkolleg Coburg – Admission & Deadlines',
            'meta_description' => 'Studienkolleg Coburg. Courses, deadlines and requirements.',
        ]);

        // ===============================
        // 6) Landesstudienkolleg Sachsen-Anhalt (HS Anhalt)
        // ===============================
        $seed([
            'name' => 'Landesstudienkolleg Sachsen-Anhalt (Hochschule Anhalt)',
            'university' => 'Hochschule Anhalt',
            'city' => 'Köthen / Bernburg / Dessau (HS Anhalt)',
            'state' => 'Saxony-Anhalt',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/6.webp',
            'card_image' => 'assets/images/studienkollegs/6.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['T', 'W', 'G']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1 / B2 for G-course as noted)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => null, 'end' => '15.11', 'note' => ''],
                'Summer Semester' => ['start' => null, 'end' => '15.06', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1',
                'G-Kurs: B2 (min. 65%) as provided',
            ],

            'application_method' => 'Direct application (HS Anhalt)',
            'application_portal_note' => null,
            'application_url' => 'https://www.hs-anhalt.de/international/bewerbung-auslaendische-studierende/studienkolleg/bewerbung.html',

            'exam_link' => 'https://www.hs-anhalt.de/international/bewerbung-auslaendische-studierende/studienkolleg/bewerbung.html',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://www.hs-anhalt.de/international/bewerbung-auslaendische-studierende/studienkolleg/startseite.html',

            'meta_title' => 'Landesstudienkolleg Sachsen-Anhalt – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at Hochschule Anhalt. Deadlines and requirements.',
        ]);

        // ===============================
        // 7) Studienkolleg HTWG Konstanz
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der HTWG Konstanz',
            'university' => 'HTWG Konstanz',
            'city' => 'Konstanz',
            'state' => 'Baden-Württemberg',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/7.webp',
            'card_image' => 'assets/images/studienkollegs/7.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['T', 'W']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => null, 'end' => '01.05', 'note' => 'as provided'],
                'Summer Semester' => ['start' => null, 'end' => '01.11', 'note' => 'as provided'],
            ],
            'requirements' => [
                'German level: B1 (online test) as provided',
            ],

            'application_method' => 'Direct application (HTWG portal)',
            'application_portal_note' => 'FAQ and documents on HTWG pages',
            'application_url' => 'https://www.htwg-konstanz.de/studium/studienkolleg-der-htwg-konstanz/startseite-studienkolleg',

            'exam_link' => 'https://www.htwg-konstanz.de/studium/studienkolleg-der-htwg-konstanz/faq',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.htwg-konstanz.de/Studienkolleg-Konstanz.107.0.html',

            'meta_title' => 'Studienkolleg HTWG Konstanz – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at HTWG Konstanz. Deadlines and requirements.',
        ]);

        // ===============================
        // 8) Studienkolleg Goethe-Universität Frankfurt
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der Goethe-Universität Frankfurt',
            'university' => 'Goethe-Universität Frankfurt am Main',
            'city' => 'Frankfurt am Main',
            'state' => 'Hesse',
            'country' => 'Germany',

            'featured' => true,
            'public' => true,
            'uni_assist' => true,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/8.webp',
            'card_image' => 'assets/images/studienkollegs/8.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['M', 'T', 'W', 'G']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                ['semester' => 'Winter Semester (WS)', 'start' => null, 'end' => '01.05', 'note' => 'as provided'],
                ['semester' => 'Summer Semester (SS)', 'start' => null, 'end' => '15.11', 'note' => 'as provided'],
            ],
            'requirements' => [
                'German level: B1',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => null,
            'application_url' => 'https://my.uni-assist.de/',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://www.uni-frankfurt.de/43662351/Studienkolleg',

            'meta_title' => 'Studienkolleg Goethe University Frankfurt – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at Goethe University Frankfurt. Requirements and deadlines.',
        ]);

        // ===============================
        // 9) Studienkolleg Universität Heidelberg
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der Universität Heidelberg',
            'university' => 'Universität Heidelberg',
            'city' => 'Heidelberg',
            'state' => 'Baden-Württemberg',
            'country' => 'Germany',

            'featured' => true,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/9.webp',
            'card_image' => 'assets/images/studienkollegs/9.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['M', 'T', 'W', 'G', 'S']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '01.05', 'end' => '30.06', 'note' => ''],
                'Summer Semester' => ['start' => '01.11', 'end' => '15.12', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
            ],

            'application_method' => 'Direct application (HeiCO portal)',
            'application_portal_note' => null,
            'application_url' => 'https://heico.uni-heidelberg.de/heiCO/ee/ui/ca2/app/desktop/#/login',

            'exam_link' => 'https://www.uni-heidelberg.de/de/studium/bewerben-einschreiben/voraussetzungen-fuer-ein-studium-0/studienkolleg-und-feststellungspruefung/bewerbung-zum-studienkolleg-bewerbungsunterlagen',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.isz.uni-heidelberg.de/d_kurse_sk.html',

            'meta_title' => 'Studienkolleg University of Heidelberg – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at Heidelberg University. Requirements and deadlines.',
        ]);

        // ===============================
        // 10) Studienkolleg TU Berlin
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der TU Berlin',
            'university' => 'Technische Universität Berlin',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'country' => 'Germany',

            'featured' => true,
            'public' => true,
            'uni_assist' => true,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/10.webp',
            'card_image' => 'assets/images/studienkollegs/10.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['T', 'TI', 'WW', 'W']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '15.03', 'end' => '30.06', 'note' => ''],
                'Summer Semester' => ['start' => '15.09', 'end' => '30.11', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => null,
            'application_url' => 'https://my.uni-assist.de/',

            'exam_link' => 'https://www.tu.berlin/international/studierende/studienkolleg/kurse-und-pruefungen',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.studienkolleg.tu-berlin.de/',

            'meta_title' => 'Studienkolleg TU Berlin – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at TU Berlin. Requirements, courses and deadlines.',
        ]);

        // ===============================
        // 11) Studienkolleg FU Berlin
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der FU Berlin',
            'university' => 'Freie Universität Berlin',
            'city' => 'Berlin',
            'state' => 'Berlin',
            'country' => 'Germany',

            'featured' => true,
            'public' => true,
            'uni_assist' => true,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/11.webp',
            'card_image' => 'assets/images/studienkollegs/11.webp',
            'university_logo' => 'https://assets.edwerk.com/universities/logos/fu_berlin.svg',
            'video_url' => 'https://www.youtube.com/embed/3b3WdGQqO-g',

            'languages' => ['German'],
            'courses' => $makeCourses(['T', 'M', 'W', 'G', 'S']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '01.06', 'end' => '15.07', 'note' => ''],
                'Summer Semester' => ['start' => '01.12', 'end' => '15.01', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => null,
            'application_url' => 'https://my.uni-assist.de/',

            'exam_link' => 'https://www.fu-berlin.de/studium/bewerbung/bachelor/hochschulinternes-verfahren-aus/index.html',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.fu-berlin.de/studienkolleg',
            'contact_email' => 'studienkolleg@fu-berlin.de',
            'address' => 'Berlin',

            'meta_title' => 'Studienkolleg FU Berlin – Admission & Requirements',
            'meta_description' => 'Studienkolleg at Freie Universität Berlin for international applicants.',
        ]);

        // ===============================
        // 12) Studienkolleg Hamburg
        // ===============================
        $seed([
            'name' => 'Studienkolleg Hamburg',
            'university' => 'Studienkolleg Hamburg',
            'city' => 'Hamburg',
            'state' => 'Hamburg',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Unknown',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/12.webp',
            'card_image' => 'assets/images/studienkollegs/12.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['M', 'T', 'W', 'G']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '01.01', 'end' => '01.03', 'note' => ''],
                'Summer Semester' => ['start' => '01.07', 'end' => '01.09', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
            ],

            'application_method' => 'Direct application (Studienkolleg portal)',
            'application_portal_note' => null,
            'application_url' => 'https://bewerbungen.studienkolleg-hh.de/',

            'exam_link' => 'https://studienkolleg-hamburg.de/bewerbung/fachkurs/bewerbungsablauf/',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.studienkolleg-hamburg.de/',

            'meta_title' => 'Studienkolleg Hamburg – Admission & Deadlines',
            'meta_description' => 'Studienkolleg Hamburg. Courses, deadlines and requirements.',
        ]);

        // ===============================
        // 13) Studienkolleg Universität Kassel
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der Universität Kassel',
            'university' => 'Universität Kassel',
            'city' => 'Kassel',
            'state' => 'Hesse',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/13.webp',
            'card_image' => 'assets/images/studienkollegs/13.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['T', 'W']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => null, 'end' => '30.04', 'note' => ''],
                'Summer Semester' => ['start' => null, 'end' => null, 'note' => 'No application possible (as provided)'],
            ],
            'requirements' => [
                'German level: B1',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => null,
            'application_url' => 'https://my.uni-assist.de/',

            'exam_link' => 'https://www.uni-kassel.de/einrichtung/studienkolleg/bewerbung-und-aufnahme',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://www.uni-kassel.de/einrichtung/studienkolleg/willkommen',

            'meta_title' => 'Studienkolleg University of Kassel – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at University of Kassel. Requirements and deadlines.',
        ]);

        // ===============================
        // 14) Studienkolleg Mittelhessen (Uni Marburg)
        // ===============================
        $seed([
            'name' => 'Studienkolleg Mittelhessen (Universität Marburg)',
            'university' => 'Philipps-Universität Marburg',
            'city' => 'Marburg',
            'state' => 'Hesse',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => true,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/14.webp',
            'card_image' => 'assets/images/studienkollegs/14.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['M', 'T', 'W', 'G']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '01.03', 'end' => '15.04', 'note' => ''],
                'Summer Semester' => ['start' => '01.09', 'end' => '15.10', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => null,
            'application_url' => 'https://my.uni-assist.de/',

            'exam_link' => 'https://www.uni-marburg.de/de/studium/bewerbung/bewerben-einschreiben/international/assist/bewerbung-und-zulassung',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.uni-marburg.de/studienkolleg',

            'meta_title' => 'Studienkolleg Marburg – Admission & Deadlines',
            'meta_description' => 'Studienkolleg Mittelhessen (Marburg). Requirements and deadlines.',
        ]);

        // ===============================
        // 15) Studienkolleg Hochschule Wismar
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der Hochschule Wismar',
            'university' => 'Hochschule Wismar',
            'city' => 'Wismar',
            'state' => 'Mecklenburg-Vorpommern',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Unknown',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/15.webp',
            'card_image' => 'assets/images/studienkollegs/15.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['TI', 'W', 'WW']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => null, 'end' => '30.06', 'note' => ''],
                'Summer Semester' => ['start' => null, 'end' => '30.11', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
            ],

            'application_method' => 'Direct application (HS Wismar)',
            'application_portal_note' => null,
            'application_url' => 'https://www.hs-wismar.de/international/aus-dem-ausland/studienkolleg/bewerbung/',

            'exam_link' => 'https://www.hs-wismar.de/international/aus-dem-ausland/studienkolleg/bewerbung/',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://www.hs-wismar.de/international/aus-dem-ausland/studienkolleg/',

            'meta_title' => 'Studienkolleg Hochschule Wismar – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at Hochschule Wismar. Requirements and deadlines.',
        ]);

        // ===============================
        // 16) Studienkolleg Universität Hannover
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der Universität Hannover',
            'university' => 'Leibniz Universität Hannover',
            'city' => 'Hannover',
            'state' => 'Lower Saxony',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => true,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/16.webp',
            'card_image' => 'assets/images/studienkollegs/16.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['G', 'S', 'M', 'T', 'W']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '15.04', 'end' => '31.05', 'note' => ''],
                'Summer Semester' => ['start' => '01.12', 'end' => '15.01', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => null,
            'application_url' => 'https://my.uni-assist.de/',

            'exam_link' => 'https://www.uni-hannover.de/de/studium/vor-dem-studium/bewerbung-zulassung/studienplatzbewerbung/bachelor-nicht-eu',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.stk.uni-hannover.de/',

            'meta_title' => 'Studienkolleg Hannover – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at Leibniz University Hannover. Requirements and deadlines.',
        ]);

        // ===============================
        // 17) Studienkolleg Johannes Gutenberg-Uni Mainz
        // ===============================
        $seed([
            'name' => 'Studienkolleg der Johannes-Gutenberg-Universität Mainz',
            'university' => 'Johannes Gutenberg-Universität Mainz',
            'city' => 'Mainz',
            'state' => 'Rhineland-Palatinate',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/17.webp',
            'card_image' => 'assets/images/studienkollegs/17.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['TI', 'T', 'WW', 'W']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '15.03', 'end' => '15.05', 'note' => ''],
                'Summer Semester' => ['start' => '15.09', 'end' => '15.11', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
            ],

            'application_method' => 'Direct application (JGU portal)',
            'application_portal_note' => null,
            'application_url' => 'https://www.studium.uni-mainz.de/meine-bewerbung/bewerbung-international/bewerbung-internationale-studierende/#bewerbung',

            'exam_link' => 'https://www.studium.uni-mainz.de/meine-bewerbung/bewerbung-international/studienkolleg/#schritt-2-bewerben-sie-sich-im-richtigen-bewerbungsverfahren',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://www.issk.uni-mainz.de/studienkolleg/',

            'meta_title' => 'Studienkolleg Mainz – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at Johannes Gutenberg University Mainz. Requirements and deadlines.',
        ]);

        // ===============================
        // 18) Internationales Studienkolleg HS Kaiserslautern
        // ===============================
        $seed([
            'name' => 'Internationales Studienkolleg der Hochschule Kaiserslautern',
            'university' => 'Hochschule Kaiserslautern',
            'city' => 'Kaiserslautern',
            'state' => 'Rhineland-Palatinate',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Unknown',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/18.webp',
            'card_image' => 'assets/images/studienkollegs/18.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['TI', 'T', 'WW', 'W']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                ['semester' => 'Winter Semester (WS)', 'start' => null, 'end' => '15.02', 'note' => 'as provided: "bis 15.02 / 15.04"'],
                ['semester' => 'Winter Semester (WS) (Alt.)', 'start' => null, 'end' => '15.04', 'note' => 'as provided'],
                ['semester' => 'Summer Semester (SS)', 'start' => null, 'end' => '15.08', 'note' => 'as provided: "bis 15.08 / 15.10"'],
                ['semester' => 'Summer Semester (SS) (Alt.)', 'start' => null, 'end' => '15.10', 'note' => 'as provided'],
            ],
            'requirements' => [
                'German level: B2',
            ],

            'application_method' => 'Direct application (QIS portal)',
            'application_portal_note' => null,
            'application_url' => 'https://qis.hs-kl.de/qisserver/rds?state=wimma&stg=a&imma=einl',

            'exam_link' => 'https://www.hs-kl.de/international/internationales-studienkolleg/bewerbung',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.hs-kl.de/internationales-studienkolleg',

            'meta_title' => 'Studienkolleg Kaiserslautern – Admission & Deadlines',
            'meta_description' => 'Internationales Studienkolleg HS Kaiserslautern. Requirements and deadlines.',
        ]);

        // ===============================
        // 19) Studienkolleg Sachsen (Uni Leipzig / TU Dresden / TU Chemnitz / TU Freiberg) – entry "Uni Leipzig"
        // ===============================
        $seed([
            'name' => 'Universität Leipzig – Studienkolleg Sachsen',
            'university' => 'Universität Leipzig (Studienkolleg Sachsen)',
            'city' => 'Leipzig',
            'state' => 'Saxony',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => true,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/19.webp',
            'card_image' => 'assets/images/studienkollegs/19.webp',
            'university_logo' => 'https://assets.edwerk.com/universities/logos/uni_leipzig.svg',
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['M', 'T', 'W', 'G', 'S']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => null, 'end' => '30.06', 'note' => 'Uni Leipzig: 15.06 (as provided). Other partners have different dates.'],
                'Summer Semester' => ['start' => null, 'end' => '15.12', 'note' => 'Uni Leipzig: 15.12 (as provided). Other partners have different dates.'],
            ],
            'requirements' => [
                'German level: B1',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => 'Deadlines vary by partner university (TU Dresden, TU Chemnitz, TU Freiberg) as provided',
            'application_url' => 'https://my.uni-assist.de/',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.uni-leipzig.de/stksachs',

            'meta_title' => 'Studienkolleg Sachsen (Uni Leipzig) – Admission & Deadlines',
            'meta_description' => 'Studienkolleg Sachsen at University of Leipzig. Requirements and deadlines.',
        ]);

        // ===============================
        // 20) Hochschule Zittau/Görlitz Studienkolleg
        // ===============================
        $seed([
            'name' => 'Hochschule Zittau/Görlitz – Studienkolleg',
            'university' => 'Hochschule Zittau/Görlitz',
            'city' => 'Zittau / Görlitz',
            'state' => 'Saxony',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Unknown',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/20.webp',
            'card_image' => 'assets/images/studienkollegs/20.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['TI', 'WW']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '01.03', 'end' => '30.04', 'note' => ''],
                'Summer Semester' => ['start' => '01.09', 'end' => '31.10', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1',
            ],

            'application_method' => 'Direct application (HSZG portal)',
            'application_portal_note' => null,
            'application_url' => 'https://bewerber.hszg.de/qisserver/pages/cs/sys/portal/hisinoneStartPage.faces?page=Bewerbende',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://studienkolleg.hszg.de/',

            'meta_title' => 'Studienkolleg HSZG – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at Hochschule Zittau/Görlitz. Requirements and deadlines.',
        ]);

        // ===============================
        // 21) Studienkolleg Martin-Luther-Universität Halle-Wittenberg
        // ===============================
        $seed([
            'name' => 'Studienkolleg an der Martin-Luther-Universität Halle-Wittenberg',
            'university' => 'Martin-Luther-Universität Halle-Wittenberg',
            'city' => 'Halle (Saale)',
            'state' => 'Saxony-Anhalt',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => true,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Free',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/21.webp',
            'card_image' => 'assets/images/studienkollegs/21.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['M', 'T', 'W', 'G', 'S']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => null, 'end' => '30.06', 'note' => ''],
                'Summer Semester' => ['start' => null, 'end' => '15.12', 'note' => ''],
            ],
            'requirements' => [
                'German level: B2',
                'uni-assist required (as provided)',
            ],

            'application_method' => 'Apply via uni-assist',
            'application_portal_note' => null,
            'application_url' => 'https://my.uni-assist.de/',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'http://www.studienkolleg.uni-halle.de/',

            'meta_title' => 'Studienkolleg Halle-Wittenberg – Admission & Deadlines',
            'meta_description' => 'Studienkolleg at MLU Halle-Wittenberg. Requirements and deadlines.',
        ]);

        // ===============================
        // 22) Studienkolleg Nordhausen / Jena / Weimar / Erfurt / Ilmenau (consortium)
        // ===============================
        $seed([
            'name' => 'Studienkolleg Nordhausen – Jena – Weimar – Erfurt – Ilmenau',
            'university' => 'Staatliches Studienkolleg (Thuringia / partner Hochschulen)',
            'city' => 'Nordhausen (partner network)',
            'state' => 'Thuringia',
            'country' => 'Germany',

            'featured' => false,
            'public' => true,
            'uni_assist' => false,
            'entrance_exam' => true,

            'duration_semesters' => 2,
            'tuition' => 'Unknown',
            'language_of_instruction' => 'German',

            'hero_image' => 'assets/images/studienkollegs/22.webp',
            'card_image' => 'assets/images/studienkollegs/22.webp',
            'university_logo' => null,
            'video_url' => null,

            'languages' => ['German'],
            'courses' => $makeCourses(['M', 'T', 'W', 'G', 'SW']),
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B1) – depends on Hochschule (as provided)',
                'Passport copy',
            ],
            'deadlines' => [
                'Winter Semester' => ['start' => '15.04', 'end' => null, 'note' => 'as provided: "ab 15.04. bis ----"'],
                'Summer Semester' => ['start' => '15.11', 'end' => '31.03', 'note' => ''],
            ],
            'requirements' => [
                'German level: B1 (depends on Hochschule) as provided',
            ],

            'application_method' => 'Direct application (HS Nordhausen pages)',
            'application_portal_note' => null,
            'application_url' => 'https://www.hs-nordhausen.de/international/staatliches-studienkolleg/studienkolleg-bewerbung/',

            'exam_link' => 'https://www.hs-nordhausen.de/international/come-in/bewerbung/direktstudierende/',

            'certification_required' => true,
            'translation_required' => false,

            'official_website' => 'https://www.hs-nordhausen.de/international/staatliches-studienkolleg/',

            'meta_title' => 'Staatliches Studienkolleg Nordhausen – Admission & Deadlines',
            'meta_description' => 'Studienkolleg Nordhausen and partner Hochschulen. Requirements and deadlines.',
        ]);
    }
}
