<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        /* =====================================================
         * 1. SITES (2 centres GLS)
         * ===================================================== */
        $marrakechSiteId = DB::table('sites')->insertGetId([
            'name' => 'GLS Sprachenzentrum Marrakech',
            'slug' => 'gls-marrakech',
            'city' => 'Marrakech',
            'address' => 'Gueliz, Avenue Mohammed V',
            'phone' => '+212 600 000 001',
            'email' => 'marrakech@glssprachenzentrum.ma',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $rabatSiteId = DB::table('sites')->insertGetId([
            'name' => 'GLS Sprachenzentrum Rabat',
            'slug' => 'gls-rabat',
            'city' => 'Rabat',
            'address' => 'Agdal, Avenue Fal Ould Oumeir',
            'phone' => '+212 600 000 002',
            'email' => 'rabat@glssprachenzentrum.ma',
            'is_active' => true,
            'created_at' => now(),
        ]);

        /* =====================================================
         * 2. TEACHERS (2 enseignants)
         * ===================================================== */
        $teacher1Id = DB::table('teachers')->insertGetId([
            'site_id' => $marrakechSiteId,
            'name' => 'Yassine Alami',
            'slug' => 'yassine-alami',
            'speciality' => 'German B2 / Telc',
            'bio' => 'Certified German teacher with 10 years of experience.',
            'email' => 'yassine@glssprachenzentrum.ma',
            'created_at' => now(),
        ]);

        $teacher2Id = DB::table('teachers')->insertGetId([
            'site_id' => $rabatSiteId,
            'name' => 'Sarah Müller',
            'slug' => 'sarah-muller',
            'speciality' => 'German A1–B1',
            'bio' => 'Native German teacher specialized in beginners.',
            'email' => 'sarah@glssprachenzentrum.ma',
            'created_at' => now(),
        ]);

        /* =====================================================
         * 3. GROUPS (2 groupes)
         * ===================================================== */
        DB::table('groups')->insert([
            [
                'site_id' => $marrakechSiteId,
                'teacher_id' => $teacher1Id,
                'name' => 'B2 Intensive Morning',
                'name_fr' => 'B2 Intensif Matin',
                'level' => 'B2',
                'period_label' => 'morning',
                'time_range' => '09:00 - 12:00',
                'status' => 'active',
                'date_debut' => now()->subMonths(1),
                'date_fin' => now()->addMonths(2),
                'created_at' => now(),
            ],
            [
                'site_id' => $rabatSiteId,
                'teacher_id' => $teacher2Id,
                'name' => 'A2 Evening Course',
                'name_fr' => 'A2 Cours du Soir',
                'level' => 'A2',
                'period_label' => 'evening',
                'time_range' => '18:30 - 20:30',
                'status' => 'upcoming',
                'date_debut' => now()->addWeeks(2),
                'date_fin' => now()->addMonths(3),
                'created_at' => now(),
            ],
        ]);

        /* =====================================================
         * 4. BLOG CATEGORIES (2 catégories)
         * ===================================================== */
        $catGermanyId = DB::table('blog_categories')->insertGetId([
            'name_fr' => 'Études en Allemagne',
            'name_en' => 'Studying in Germany',
            'slug' => 'etudes-allemagne',
            'is_active' => true,
            'position' => 1,
            'created_at' => now(),
        ]);

        $catLanguageId = DB::table('blog_categories')->insertGetId([
            'name_fr' => 'Apprendre l’allemand',
            'name_en' => 'Learn German',
            'slug' => 'apprendre-allemand',
            'is_active' => true,
            'position' => 2,
            'created_at' => now(),
        ]);

        /* =====================================================
         * 5. BLOG POSTS (2 articles)
         * ===================================================== */
        DB::table('blog_posts')->insert([
            [
                'category_id' => $catGermanyId,
                'title_fr' => 'Comment réussir son examen B2',
                'title_en' => 'How to pass your B2 exam',
                'slug' => 'reussir-examen-b2',
                'content_fr' => 'Conseils pratiques pour réussir le niveau B2.',
                'content_en' => 'Practical tips to succeed at B2 level.',
                'reading_time' => 4,
                'featured' => true,
                'views' => 120,
                'status' => 'published',
                'created_at' => now(),
            ],
            [
                'category_id' => $catLanguageId,
                'title_fr' => 'Pourquoi apprendre l’allemand au Maroc',
                'title_en' => 'Why learn German in Morocco',
                'slug' => 'apprendre-allemand-maroc',
                'content_fr' => 'L’allemand ouvre de nombreuses opportunités.',
                'content_en' => 'German opens many opportunities.',
                'reading_time' => 3,
                'featured' => false,
                'views' => 85,
                'status' => 'published',
                'created_at' => now(),
            ],
        ]);

        /* =====================================================
         * 6. STUDIENKOLLEGS (2 établissements)
         * ===================================================== */
        DB::table('studienkollegs')->insert([
            [
                'name' => 'Studienkolleg München',
                'slug' => 'studienkolleg-munich',
                'university' => 'Ludwig-Maximilians-Universität',
                'city' => 'Munich',
                'country' => 'Germany',
                'featured' => true,
                'public' => true,
                'tuition' => 'Free',
                'language_of_instruction' => 'German',
                'courses' => json_encode(['T-Kurs', 'W-Kurs']),
                'languages' => json_encode(['German B2']),
                'documents' => json_encode(['B2 Certificate', 'High School Diploma']),
                'deadlines' => json_encode([
                    ['semester' => 'Winter', 'date' => '15.07'],
                    ['semester' => 'Summer', 'date' => '15.01'],
                ]),
                'created_at' => now(),
            ],
            [
                'name' => 'Studienkolleg Berlin',
                'slug' => 'studienkolleg-berlin',
                'university' => 'Technische Universität Berlin',
                'city' => 'Berlin',
                'country' => 'Germany',
                'featured' => false,
                'public' => true,
                'tuition' => 'Free',
                'language_of_instruction' => 'German',
                'courses' => json_encode(['M-Kurs']),
                'languages' => json_encode(['German B1+']),
                'documents' => json_encode(['Language Certificate', 'School Diploma']),
                'deadlines' => json_encode([
                    ['semester' => 'Winter', 'date' => '01.07'],
                ]),
                'created_at' => now(),
            ],
        ]);

        /* =====================================================
         * 7. CERTIFICATES (2 certificats)
         * ===================================================== */
        DB::table('certificates')->insert([
            [
                'last_name' => 'Benani',
                'first_name' => 'Amine',
                'birth_date' => '2000-05-15',
                'birth_place' => 'Casablanca',
                'certificate_number' => 'GLS-B2-0001',
                'exam_level' => 'Deutsch B2',
                'exam_date' => '2023-10-10',
                'issue_date' => '2023-11-01',
                'reading_score' => 60,
                'grammar_score' => 25,
                'listening_score' => 65,
                'writing_score' => 40,
                'written_total' => 190,
                'presentation_score' => 20,
                'discussion_score' => 22,
                'problemsolving_score' => 21,
                'oral_total' => 63,
                'final_result' => 'Gut',
                'created_at' => now(),
            ],
            [
                'last_name' => 'El Idrissi',
                'first_name' => 'Sara',
                'birth_date' => '2001-03-20',
                'birth_place' => 'Rabat',
                'certificate_number' => 'GLS-A2-0002',
                'exam_level' => 'Deutsch A2',
                'exam_date' => '2023-09-15',
                'issue_date' => '2023-10-01',
                'reading_score' => 55,
                'grammar_score' => 20,
                'listening_score' => 50,
                'writing_score' => 38,
                'written_total' => 163,
                'presentation_score' => 18,
                'discussion_score' => 19,
                'problemsolving_score' => 17,
                'oral_total' => 54,
                'final_result' => 'Befriedigend',
                'created_at' => now(),
            ],
        ]);

        /* =====================================================
         * 8. INSCRIPTIONS (2 leads)
         * ===================================================== */
        DB::table('gls_inscriptions')->insert([
            [
                'name' => 'Youssef Ait Ali',
                'email' => 'youssef@gmail.com',
                'phone' => '+212 612 345 678',
                'adresse' => 'Marrakech',
                'niveau' => 'B1',
                'type_cours' => 'Intensive',
                'horaire_prefere' => 'Morning',
                'date_start' => now()->addWeeks(2),
                'centre' => 'GLS Marrakech',
                'created_at' => now(),
            ],
            [
                'name' => 'Imane Rahmani',
                'email' => 'imane@gmail.com',
                'phone' => '+212 623 456 789',
                'adresse' => 'Rabat',
                'niveau' => 'A2',
                'type_cours' => 'Evening',
                'horaire_prefere' => 'Evening',
                'date_start' => now()->addWeeks(3),
                'centre' => 'GLS Rabat',
                'created_at' => now(),
            ],
        ]);
    }
}
