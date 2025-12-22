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

        // ===============================
        // 1. Studienkolleg FU Berlin
        // ===============================
        Studienkolleg::create([
            'name' => 'Studienkolleg der FU Berlin',
            'slug' => Str::slug('Studienkolleg der FU Berlin'),
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

            'hero_image' => 'assets/images/studienkollegs/12.webp',
            'card_image' => 'assets/images/studienkollegs/12.webp',
            'university_logo' => 'https://assets.edwerk.com/universities/logos/fu_berlin.svg',
            'video_url' => 'https://www.youtube.com/embed/3b3WdGQqO-g',

            'application_url' => 'https://www.uni-assist.de/apply/',
            'exam_url' => 'https://www.fu-berlin.de/en/studium/international/studienkolleg/aufnahmepruefung.html',
            'official_website' => 'https://www.fu-berlin.de/en/studium/international/studienkolleg',

            'languages' => ['German'],
            'courses' => ['T Course', 'W Course', 'M Course', 'G Course'],
            'documents' => [
                'School leaving certificate',
                'Transcript of records',
                'German language certificate (B2)',
                'Passport copy',
                'CV (recommended)',
            ],

            'deadlines' => [
                [
                    'semester' => 'Winter Semester (WS)',
                    'start' => '01.06',
                    'end' => '15.07',
                    'note' => 'Only intake period',
                ],
            ],

            'contact_email' => 'studienkolleg@fu-berlin.de',
            'address' => 'Malteserstraße 74–100, Berlin',

            'meta_title' => 'Studienkolleg der FU Berlin – Admission & Requirements',
            'meta_description' => 'Studienkolleg der FU Berlin prepares international students for university studies in Germany.',
        ]);

        // ===============================
        // 2. Studienkolleg Leipzig
        // ===============================
        Studienkolleg::create([
            'name' => 'Studienkolleg Leipzig',
            'slug' => Str::slug('Studienkolleg Leipzig'),
            'university' => 'University of Leipzig',
            'city' => 'Leipzig',
            'state' => 'Saxony',
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
            'university_logo' => 'https://assets.edwerk.com/universities/logos/uni_leipzig.svg',

            'application_url' => 'https://www.uni-leipzig.de/en/international/studienkolleg/',
            'official_website' => 'https://www.uni-leipzig.de',

            'languages' => ['German'],
            'courses' => ['T Course', 'W Course', 'M Course'],
            'documents' => [
                'High school diploma',
                'German certificate B1/B2',
                'Passport',
            ],

            'deadlines' => [
                [
                    'semester' => 'Winter Semester (WS)',
                    'start' => '01.05',
                    'end' => '30.06',
                ],
                [
                    'semester' => 'Summer Semester (SS)',
                    'start' => '01.11',
                    'end' => '15.12',
                ],
            ],

            'contact_email' => 'studienkolleg@uni-leipzig.de',
            'address' => 'Goethestraße 6, Leipzig',

            'meta_title' => 'Studienkolleg Leipzig – Public Studienkolleg in Germany',
            'meta_description' => 'Studienkolleg Leipzig offers preparatory courses for international students.',
        ]);

        // ===============================
        // 3. Studienkolleg TU Darmstadt
        // ===============================
        Studienkolleg::create([
            'name' => 'Studienkolleg TU Darmstadt',
            'slug' => Str::slug('Studienkolleg TU Darmstadt'),
            'university' => 'Technical University of Darmstadt',
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
            'university_logo' => 'https://assets.edwerk.com/universities/logos/tu_darmstadt.svg',

            'application_url' => 'https://www.tu-darmstadt.de',
            'official_website' => 'https://www.tu-darmstadt.de',

            'languages' => ['German'],
            'courses' => ['T Course', 'W Course'],
            'documents' => [
                'Secondary school certificate',
                'German certificate B2',
                'Passport',
            ],

            'deadlines' => [
                [
                    'semester' => 'Winter Semester (WS)',
                    'start' => '01.06',
                    'end' => '15.07',
                ],
            ],

            'contact_email' => 'studienkolleg@tu-darmstadt.de',
            'address' => 'Karolinenplatz 5, Darmstadt',

            'meta_title' => 'Studienkolleg TU Darmstadt – Requirements & Admission',
            'meta_description' => 'Studienkolleg TU Darmstadt prepares students for technical university studies in Germany.',
        ]);
    }
}
