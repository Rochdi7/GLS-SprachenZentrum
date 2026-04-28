<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WeeklyReport;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WeeklySkillReportSeeder extends Seeder
{
    public function run(): void
    {
        $createdBy = User::first()?->id;

        // Pull teachers that have at least 2 groups (skills mode triggers for these).
        $multiTeachers = Teacher::has('groups', '>=', 2)->with('groups')->get();

        if ($multiTeachers->isEmpty()) {
            $this->command->warn('Aucun enseignant avec 2+ groupes trouvé. Lancez DemoDataSeeder d\'abord.');
            return;
        }

        // Skill content pools — realistic activities per skill (German B1-B2 level).
        $contentBySkill = [
            'lesen' => [
                'Lecture d\'un article de Süddeutsche Zeitung sur le changement climatique + questions de compréhension.',
                'Texte du Kursbuch Aspekte B2, Kapitel 4 — analyse en groupe + résumé écrit.',
                'Compréhension écrite — extrait littéraire (Bernhard Schlink) — vocabulaire + thèmes principaux.',
                'Test Leseverstehen Telc B1 — entraînement en conditions réelles (90 minutes).',
                'Lecture cursive : "Tschick" de Wolfgang Herrndorf — chapitres 1-3, discussion.',
                'Article de presse Die Zeit — les jeunes et les réseaux sociaux. Identification d\'arguments.',
            ],
            'hoeren' => [
                'Podcast Deutsche Welle "Langsam gesprochene Nachrichten" — prise de notes + questions.',
                'Hörverstehen Goethe-Zertifikat B1 — écoute d\'un dialogue, repérage d\'informations clés.',
                'Vidéo TED Talk en allemand sous-titré — Sprechgeschwindigkeit B2, débat après visionnage.',
                'Test d\'écoute Telc — annonces de gare, météo, nouvelles courtes. Correction collective.',
                'Émission radio Bayerischer Rundfunk — interview d\'un chef étoilé. Vocabulaire culinaire.',
                'Chanson de Wir sind Helden "Denkmal" — analyse des paroles + exercice à trous.',
            ],
            'grammatik' => [
                'Konjunktiv II — formation, usage hypothétique. Exercices d\'application contextualisés.',
                'Passiv und Passiv-Ersatzformen — transformations actif/passif, exercices intensifs.',
                'Nebensätze mit "obwohl", "während", "indem" — connecteurs logiques + production écrite.',
                'Präpositionen mit Genitiv (während, trotz, wegen, statt) — règles + exercices d\'ancrage.',
                'Adjektivdeklination — révision complète des trois types (mit/ohne Artikel, gemischt).',
                'Verben mit festen Präpositionen — mémorisation des combinaisons les plus fréquentes.',
            ],
            'schreiben' => [
                'Lettre formelle — réclamation à un magasin en ligne. Structure + formules de politesse.',
                'Essai argumentatif (Erörterung) sur "Sollte das Smartphone in der Schule verboten werden?".',
                'E-Mail à un ami pour proposer un voyage à Berlin — registre informel + Konjunktiv II.',
                'Résumé d\'un article de journal en 150 mots — entraînement à la concision.',
                'Bewerbungsschreiben (lettre de motivation) — exercice ciblé pour le marché allemand.',
                'Description d\'une image — vocabulaire descriptif + structures comparatives.',
            ],
            'sprechen' => [
                'Présentation orale (5 min) — "Mein Lieblingsbuch" — chaque étudiant + questions du groupe.',
                'Jeu de rôle : entretien d\'embauche en allemand — simulation + feedback du groupe.',
                'Débat structuré : "Pro und Contra Homeoffice" — équipes, arguments, réfutation.',
                'Description d\'images type Telc B1 — entraînement à deux + correction de la prononciation.',
                'Discussion ouverte — "Was würdest du machen, wenn...?" pour pratiquer le Konjunktiv II.',
                'Speed-talking — 2 minutes par sujet imposé pour gagner en fluidité.',
            ],
        ];

        // Optional attachment file names (simulated only — paths won't actually exist).
        $attachmentSamples = [
            'fiche-grammaire-konjunktiv2.pdf',
            'exercices-passiv-b2.pdf',
            'corrige-leseverstehen-week.pdf',
            'support-presentation-orale.pdf',
            'kursbuch-aspekte-kap4.pdf',
        ];

        // Seed: current week + 2 previous weeks.
        $startMonday = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(2);
        $created = 0;
        $skipped = 0;

        for ($week = 0; $week < 3; $week++) {
            $monday = $startMonday->copy()->addWeeks($week);

            foreach ($multiTeachers as $teacher) {
                // For each teacher, seed 2-3 random week days (skip weekends).
                $daysToSeed = collect([0, 1, 2, 3, 4])->shuffle()->take(rand(2, 3));

                foreach ($daysToSeed as $dayOffset) {
                    $date = $monday->copy()->addDays($dayOffset);

                    // Don't seed future dates
                    if ($date->isAfter(Carbon::today())) {
                        $skipped++;
                        continue;
                    }

                    // For each of teacher's groups, generate 5 skill entries (one per skill).
                    foreach ($teacher->groups as $group) {
                        foreach (array_keys(WeeklyReport::SKILLS) as $skillKey) {
                            // 80% chance of having content for this skill (sometimes a skill is skipped)
                            if (rand(1, 100) > 80) continue;

                            $pool = $contentBySkill[$skillKey];
                            $notes = $pool[array_rand($pool)];

                            $payload = [
                                'teacher_id'  => $teacher->id,
                                'group_id'    => $group->id,
                                'skill'       => $skillKey,
                                'report_date' => $date->format('Y-m-d'),
                                'notes'       => $notes,
                                'created_by'  => $createdBy,
                            ];

                            // 25% chance of having an attachment (filename only — real file not created).
                            if (rand(1, 100) <= 25) {
                                $payload['attachment_original_name'] = $attachmentSamples[array_rand($attachmentSamples)];
                                $payload['attachment_path'] = 'weekly-reports/seed-' . uniqid() . '.pdf';
                            }

                            // Avoid duplicate (teacher,group,skill,date) combinations from re-runs.
                            WeeklyReport::firstOrCreate(
                                [
                                    'teacher_id'  => $payload['teacher_id'],
                                    'group_id'    => $payload['group_id'],
                                    'skill'       => $payload['skill'],
                                    'report_date' => $payload['report_date'],
                                ],
                                $payload
                            );
                            $created++;
                        }
                    }
                }
            }
        }

        $this->command->info("✓ {$created} rapports skill-based créés pour {$multiTeachers->count()} enseignants multi-groupes sur 3 semaines.");
        if ($skipped > 0) {
            $this->command->info("  (Sauté {$skipped} jour(s) dans le futur.)");
        }
    }
}
