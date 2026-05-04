<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            
            $quizzes = [

                // =====================================================================
                //  A1 – Grundlagen: Begrüßung, Vorstellen, Artikel, Zahlen, Alltag
                // =====================================================================
                'A1' => [
                    'title'                => 'Deutsch Einstufungstest A1',
                    'description'          => 'Grundlagen: einfache Sätze, Alltag, Vorstellen, Fragen beantworten.',
                    'time_limit_seconds'   => 900,
                    'questions_per_attempt' => 15,
                    'questions'            => [
                        [
                            'question_text' => 'Welche Antwort ist richtig? „Ich ____ Adam."',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 1,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'heiße',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'heißt',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'heißen',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'heißt du', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wie fragt man nach dem Namen?',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 2,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Wie heißt du?',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Wo wohnst du?',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Wie alt bist du?','is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Was machst du?',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Antwort passt? „Woher kommst du?"',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 3,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Ich komme aus Marokko.',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich wohne zwei Zimmer.',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich heiße Marokko.',        'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich bin nach Hause.',       'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Artikel ist richtig? ____ Haus',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 4,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'das', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'der', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'die', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'den', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Zahl ist „zwölf"?',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 5,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => '12', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => '20', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => '2',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => '11', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Mein Bruder ____ 10 Jahre alt."',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 6,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'ist',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'bin',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'bist', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'sind', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Artikel ist richtig? ____ Frau',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 7,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'die', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'der', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'das', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'den', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was ist die richtige Antwort? „Wie geht es Ihnen?"',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 8,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Danke, gut.',           'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich bin aus Berlin.',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ja, bitte.',            'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich heiße Peter.',      'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Wir ____ aus Deutschland."',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 9,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'kommen', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'komme',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'kommst', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'kommt',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Farbe hat der Himmel normalerweise?',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 10,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'blau',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'rot',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'grün',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'gelb',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „____ du Kaffee oder Tee?"',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 11,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Möchtest', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Möchte',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Möchten',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Möchtet',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Tag kommt nach Montag?',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 12,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Dienstag',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Mittwoch',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Sonntag',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Freitag',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Ich ____ gern Fußball."',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 13,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'spiele', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'spielst','is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'spielt', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'spielen','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Artikel ist richtig? ____ Tisch',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 14,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'der', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'die', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'das', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'den', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Das ____ meine Mutter."',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 15,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'ist',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'sind', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'bin',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'seid', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was ist das Gegenteil von „groß"?',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 16,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'klein',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'lang',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'breit',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'schnell','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Er ____ ein Buch."',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 17,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'liest',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'lese',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'lesen',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'lest',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welches Wort passt nicht? „Apfel, Banane, Stuhl, Orange"',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 18,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Stuhl',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Apfel',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Banane', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Orange', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was sagt man, wenn man sich verabschiedet?',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 19,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Auf Wiedersehen!', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Guten Morgen!',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Willkommen!',      'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Entschuldigung!',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „____ ihr heute Abend frei?"',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 20,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Seid',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Sind',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Bist',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Bin',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Uhrzeit ist „halb drei"?',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 21,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => '2:30', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => '3:30', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => '3:00', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => '2:00', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Ich ____ einen Hund und eine Katze."',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 22,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'habe',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'hast',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'hat',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'haben', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Antwort passt? „Wo wohnst du?"',
                            'difficulty'    => 1, 'points' => 1, 'sort_order' => 23,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Ich wohne in Berlin.',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich heiße Berlin.',       'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich komme nach Berlin.',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich gehe Berlin.',        'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Die Kinder ____ im Garten."',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 24,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'spielen', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'spielt',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'spiele',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'spielst', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was ist richtig? „Ich trinke ____ Wasser."',
                            'difficulty'    => 2, 'points' => 1, 'sort_order' => 25,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'ein Glas', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'einen Glas','is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'einer Glas','is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'einem Glas','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],

                // =====================================================================
                //  A2 – Erweiterte Grundlagen: Perfekt, Modalverben, Wechselpräpositionen
                // =====================================================================
                'A2' => [
                    'title'                => 'Deutsch Einstufungstest A2',
                    'description'          => 'Erweiterte Grundlagen: Alltagssituationen, einfache Grammatik, kurze Texte.',
                    'time_limit_seconds'   => 1080,
                    'questions_per_attempt' => 15,
                    'questions'            => [
                        [
                            'question_text' => 'Welche Antwort ist richtig? „Gestern ____ ich zu Hause."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 1,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'war',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'bin',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'ist',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'wäre', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wähle die richtige Präposition: „Ich gehe ____ die Schule."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 2,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'in',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'auf', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'bei', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'mit', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Satzstellung ist richtig?',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 3,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Morgen gehe ich einkaufen.',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Gehe ich morgen einkaufen.',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich morgen gehe einkaufen.',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Einkaufen gehe morgen ich.',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Form ist richtig? „Er ____ nicht kommen."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 4,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'kann',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'können', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'kannst', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'könnt',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Antwort passt? „Was hast du am Wochenende gemacht?"',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 5,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Ich habe meine Familie besucht.',     'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich besuche morgen meine Familie.',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich werde meine Familie.',            'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich bin meine Familie besuchen.',     'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Du ____ heute nicht arbeiten."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 6,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'musst',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'muss',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'müssen', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'müsst',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wähle das richtige Perfekt: „Sie ____ nach Wien gefahren."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 7,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'ist',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'hat',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'sind', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'war',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Das Bild hängt ____ der Wand."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 8,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'an',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'auf',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'in',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'über', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was ist richtig? „Ich habe ____ Bruder."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 9,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'einen',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'ein',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'einer',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'einem',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Wir ____ gestern ins Kino gegangen."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 10,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'sind',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'haben', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'waren', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'seid',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Präposition passt? „Ich warte ____ dich."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 11,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'auf',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'für',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'an',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'über', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welches Wort fehlt? „Ich möchte ____ Arzt werden."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 12,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'gern',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'gerne',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'lieber', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'am liebsten', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Er hat sich ____ das Geschenk gefreut."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 13,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'über', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'auf',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'für',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'an',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was ist richtig? „Ich stelle das Buch ____ den Tisch."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 14,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'auf',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'an',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'in',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'unter', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welches Verb passt? „Der Zug ____ um 8 Uhr abgefahren."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 15,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'ist',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'hat',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'war',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'wird', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Meine Schwester ist ____ als ich."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 16,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'älter',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'alt',      'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'älteste',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'ältester', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was ist richtig? „Ich muss ____ Hause gehen."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 17,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'nach', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'zu',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'in',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'bei',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Wem gehört das Buch? — Das gehört ____."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 18,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'mir',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'mich',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'ich',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'mein',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Konjunktion passt? „Ich bleibe zu Hause, ____ ich bin krank."',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 19,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'denn',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'weil',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'obwohl', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'damit',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Er hat mir ____ Buch geschenkt."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 20,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'ein',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'einen',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'einem',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'einer',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was passt? „Wenn es regnet, ____ ich einen Regenschirm mit."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 21,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'nehme',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'nimmst',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'nimmt',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'nehmen',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Ich interessiere ____ für Musik."',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 22,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'mich', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'mir',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'sich', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'uns',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz ist im Perfekt korrekt?',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 23,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Ich habe einen Brief geschrieben.',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich bin einen Brief geschrieben.',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich habe einen Brief schreiben.',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich hatte einen Brief geschrieben.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „____ du schon einmal in Österreich gewesen?"',
                            'difficulty'    => 3, 'points' => 2, 'sort_order' => 24,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Bist',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Hast',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Warst', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Wirst', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welches Verb ist trennbar?',
                            'difficulty'    => 2, 'points' => 2, 'sort_order' => 25,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'aufstehen',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'verstehen',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'beginnen',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'bekommen',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],

                // =====================================================================
                //  B1 – Mittelstufe: Nebensätze, Konjunktiv II, Passiv, Textverständnis
                // =====================================================================
                'B1' => [
                    'title'                => 'Deutsch Einstufungstest B1',
                    'description'          => 'Mittelstufe: Meinungen ausdrücken, Erfahrungen berichten, längere Sätze.',
                    'time_limit_seconds'   => 1200,
                    'questions_per_attempt' => 15,
                    'questions'            => [
                        [
                            'question_text' => 'Welche Konjunktion passt? „Ich bleibe zu Hause, ____ es regnet."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 1,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'weil', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'aber', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'und',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'oder', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wähle die richtige Form: „Wenn ich Zeit ____ , komme ich vorbei."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 2,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'habe',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'hatte', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'hätte', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'haben', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche indirekte Frage ist richtig?',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 3,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Kannst du mir sagen, wo die Bank ist?',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Kannst du mir sagen, wo ist die Bank?',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Kannst du mir sagen, ist wo die Bank?',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Kannst du mir sagen, die Bank wo ist?',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Aussage ist korrekt?',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 4,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Ich interessiere mich für Sprachen.',     'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich interessiere mir für Sprachen.',      'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich interessiere für mich Sprachen.',     'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich interessiere mich auf Sprachen.',     'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Zeitform passt? „Früher ____ ich oft ins Kino gegangen."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 5,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'bin',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'habe', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'war',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'werde','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Er sagte, ____ er morgen kommt."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 6,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'dass', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'das',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'weil', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'denn', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz ist im Passiv korrekt?',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 7,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Das Haus wird gebaut.',       'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Das Haus wird bauen.',        'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Das Haus ist bauen.',         'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Das Haus hat gebaut werden.', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Wenn ich reich ____, würde ich reisen."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 8,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'wäre',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'bin',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'war',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'würde', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Präposition verlangt den Dativ? „Ich gehe ____ dem Arzt."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 9,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'zu',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'für',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'gegen', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'um',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was bedeutet „trotzdem"?',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 10,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'dennoch / aber doch',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'deshalb / darum',      'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'außerdem / zusätzlich','is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'stattdessen / anstatt','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Er hat mich gebeten, ihm ____ helfen."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 11,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'zu',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'für',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'beim', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'zum',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Relativsatz ist korrekt?',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 12,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Der Mann, der dort steht, ist mein Lehrer.',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Der Mann, das dort steht, ist mein Lehrer.',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Der Mann, den dort steht, ist mein Lehrer.',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Der Mann, dem dort steht, ist mein Lehrer.',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Ich habe keine Lust, ____ Hause zu gehen."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 13,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'nach', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'zu',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'in',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'bei',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was drückt der Konjunktiv II aus? „Ich würde gern verreisen."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 14,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'einen Wunsch',        'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'eine Tatsache',       'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'eine Vergangenheit',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'eine Aufforderung',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Die Frau, ____ ich gestern getroffen habe, ist Ärztin."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 15,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'die',     'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'der',     'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'den',     'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'dessen',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz ist richtig? „Obwohl es kalt ist, ____."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 16,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'gehe ich spazieren',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'ich gehe spazieren',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'gehen ich spazieren',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'spazieren gehe ich',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Je mehr ich lerne, ____ besser verstehe ich."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 17,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'desto', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'je',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'so',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'als',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was ist die Bedeutung von „sich entscheiden für"?',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 18,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'eine Wahl treffen',        'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'sich entschuldigen',       'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'etwas ablehnen',           'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'sich beschweren',          'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Nachdem ich gegessen ____, bin ich spazieren gegangen."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 19,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'hatte',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'habe',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'hätte',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'bin',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Verbform passt? „Das Fenster ____ von dem Kind geöffnet."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 20,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'wurde',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'wird',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'war',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'hat',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Ich freue mich ____, dich wiederzusehen."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 21,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'darauf',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'darüber', 'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'dafür',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'damit',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz hat die richtige Wortstellung?',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 22,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Ich weiß nicht, ob er morgen kommt.',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich weiß nicht, ob er kommt morgen.',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich weiß nicht, ob kommt er morgen.',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich weiß nicht, ob morgen er kommt.',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Er arbeitet hart, ____ er Erfolg haben will."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 23,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'weil',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'obwohl',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'während', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'bevor',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Ausdruck passt? „Meiner Meinung ____ ist das richtig."',
                            'difficulty'    => 3, 'points' => 3, 'sort_order' => 24,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'nach',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'von',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'für',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'über',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Anstatt zu lernen, ____ er den ganzen Tag fern."',
                            'difficulty'    => 4, 'points' => 3, 'sort_order' => 25,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'sieht',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'sehen',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'gesehen',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'sah',      'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],

                // =====================================================================
                //  B2 – Obere Mittelstufe: Konjunktiv I, Nominalisierung, Passiv-Varianten,
                //        gehobener Wortschatz, Textanalyse
                // =====================================================================
                'B2' => [
                    'title'                => 'Deutsch Einstufungstest B2',
                    'description'          => 'Obere Mittelstufe: komplexere Strukturen, Synonyme, Textverständnis.',
                    'time_limit_seconds'   => 1500,
                    'questions_per_attempt' => 15,
                    'questions'            => [
                        [
                            'question_text' => 'Welche Formulierung ist stilistisch am besten?',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 1,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Ich bin der Ansicht, dass diese Maßnahme sinnvoll ist.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Ich denke, dass ist gut Maßnahme.',                      'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Ich Meinung habe, dass diese Maßnahme ist sinnvoll.',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Ich bin Ansicht, dass diese Maßnahme sinnvoll.',          'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Wähle das passende Wort: „Die Ergebnisse sind ____ überzeugend."',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 2,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'äußerst', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'außen',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'äußern',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'außer',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Konstruktion ist korrekt?',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 3,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Je mehr man übt, desto besser wird man.',     'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Je mehr man übt, je besser wird man.',        'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Desto mehr man übt, je besser wird man.',     'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Je mehr man übt, besser wird man desto.',     'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Umformung ist richtig?',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 4,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Obwohl er krank war, ging er zur Arbeit.',     'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Obwohl er war krank, ging er zur Arbeit.',     'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Obwohl krank er war, ging er zur Arbeit.',     'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Obwohl er krank, er ging zur Arbeit.',         'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz ist grammatikalisch korrekt?',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 5,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Er behauptet, er habe davon nichts gewusst.',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Er behauptet, er hat davon nichts gewusst.',     'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Er behauptet, er hätte davon nichts wusste.',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Er behauptet, er haben davon nichts gewusst.',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „____ der hohen Kosten hat sich das Projekt gelohnt."',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 6,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Trotz',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Wegen',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Während',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Aufgrund', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welches Synonym passt am besten für „erheblich"?',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 7,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'beträchtlich',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'erhaben',       'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'erhoben',       'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'erhalten',      'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Nominalisierung ist korrekt? „Man muss pünktlich sein." → „____"',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 8,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Pünktlichkeit ist wichtig.',             'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Pünktlich sein ist das Wichtigste.',     'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Man pünktlich sein muss.',               'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Das Pünktlich ist notwendig.',            'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Der Bericht ist ____ Berücksichtigung aller Faktoren erstellt worden."',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 9,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'unter',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'mit',     'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'durch',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'bei',     'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz drückt eine irreale Bedingung in der Vergangenheit aus?',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 10,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Wenn ich das gewusst hätte, wäre ich gekommen.',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Wenn ich das wüsste, käme ich.',                  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Wenn ich das weiß, komme ich.',                   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Wenn ich das wusste, bin ich gekommen.',           'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Die Entscheidung ____ noch aussteht, können wir nicht handeln."',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 11,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => ', die',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => ', das',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => ', welcher','is_correct' => false, 'sort_order' => 2],
                                ['option_text' => ', deren',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Redewendung bedeutet „sich große Mühe geben"?',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 12,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'sich ins Zeug legen',       'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'auf den Arm nehmen',        'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'den Nagel auf den Kopf treffen', 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'jemandem auf der Nase herumtanzen', 'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Er sprach, ____ ob er alles wüsste."',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 13,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'als',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'wie',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'wenn',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'dass',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz verwendet das Partizip I korrekt als Attribut?',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 14,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Die steigenden Preise beunruhigen die Verbraucher.',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Die gestiegenden Preise beunruhigen die Verbraucher.','is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Die steigende Preise beunruhigt die Verbraucher.',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Die steigen Preise beunruhigen die Verbraucher.',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Es ist fraglich, ____ die Reform Erfolg haben wird."',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 15,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'ob',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'dass',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'weil',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'wenn',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was bedeutet „in Kauf nehmen"?',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 16,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'etwas Negatives akzeptieren',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'etwas kaufen',                   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'etwas anbieten',                 'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'etwas verkaufen',                'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Die Studie, ____ Ergebnisse kürzlich veröffentlicht wurden, ist umstritten."',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 17,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'deren',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'dessen',  'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'die',     'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'welche',  'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Passiv-Ersatzform ist korrekt? „Das lässt sich leicht ____."',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 18,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'erklären',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'erklärt',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'erklärend',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'zu erklären','is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Nicht nur die Qualität, ____ auch der Preis spielt eine Rolle."',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 19,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'sondern',  'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'aber',     'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'jedoch',   'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'denn',     'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz enthält einen Konjunktiv I (indirekte Rede)?',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 20,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Die Ministerin sagte, sie sei zuversichtlich.',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Die Ministerin sagte, sie wäre zuversichtlich.',   'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Die Ministerin sagte, sie ist zuversichtlich.',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Die Ministerin sagte, sie war zuversichtlich.',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Infolge ____ schlechten Wetters wurde die Veranstaltung abgesagt."',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 21,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'des',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'dem',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'den',    'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'der',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Was bedeutet „auf Anhieb"?',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 22,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'sofort, beim ersten Versuch',    'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'nach langer Überlegung',         'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'mit Hilfe anderer',              'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'am Ende',                        'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welcher Satz ist korrekt? „Die Firma, bei ____ ich arbeite, expandiert."',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 23,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'der',      'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'die',      'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'dem',      'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'dessen',   'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Ergänze: „Es ____ sich heraus, dass die Hypothese falsch war."',
                            'difficulty'    => 4, 'points' => 4, 'sort_order' => 24,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'stellte',   'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'stellt',    'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'gestellt',  'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'stelle',    'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                        [
                            'question_text' => 'Welche Formulierung drückt eine Konzession aus?',
                            'difficulty'    => 5, 'points' => 4, 'sort_order' => 25,
                            'media_type'    => 'none', 'media_caption' => null,
                            'options'       => [
                                ['option_text' => 'Wenngleich die Kritik berechtigt ist, bleibt das Ergebnis positiv.', 'is_correct' => true,  'sort_order' => 0],
                                ['option_text' => 'Weil die Kritik berechtigt ist, bleibt das Ergebnis positiv.',       'is_correct' => false, 'sort_order' => 1],
                                ['option_text' => 'Sobald die Kritik berechtigt ist, bleibt das Ergebnis positiv.',     'is_correct' => false, 'sort_order' => 2],
                                ['option_text' => 'Falls die Kritik berechtigt ist, bleibt das Ergebnis positiv.',      'is_correct' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],
            ];

            /* ================================================================
             *  1) Create / Update quizzes (level unique)
             * ================================================================ */
            $quizIds = [];

            foreach ($quizzes as $level => $quizData) {
                $quiz = Quiz::query()->updateOrCreate(
                    ['level' => $level],
                    [
                        'title'                => $quizData['title'],
                        'description'          => $quizData['description'] ?? null,
                        'time_limit_seconds'   => $quizData['time_limit_seconds'] ?? null,
                        'questions_per_attempt' => $quizData['questions_per_attempt'] ?? 10,
                        'is_active'            => true,
                    ]
                );

                $quizIds[$level] = $quiz->id;
            }

            /* ================================================================
             *  2) Cleanup (questions / options + Spatie media) for these quizzes
             * ================================================================ */
            $ids = array_values($quizIds);

            $existingQuestions = QuizQuestion::query()
                ->whereIn('quiz_id', $ids)
                ->with(['options.media', 'media'])
                ->get();

            foreach ($existingQuestions as $q) {
                $q->clearMediaCollection('question_image');
                $q->clearMediaCollection('question_audio');

                foreach ($q->options as $opt) {
                    $opt->clearMediaCollection('option_image');
                }
            }

            QuizOption::query()
                ->whereIn('question_id', $existingQuestions->pluck('id'))
                ->delete();

            QuizQuestion::query()
                ->whereIn('quiz_id', $ids)
                ->delete();

            /* ================================================================
             *  3) Insert fresh questions / options
             * ================================================================ */
            foreach ($quizzes as $level => $quizData) {
                $quizId = $quizIds[$level];

                foreach ($quizData['questions'] as $qData) {

                    $question = QuizQuestion::query()->create([
                        'quiz_id'       => $quizId,
                        'question_text' => $qData['question_text'],
                        'media_type'    => $qData['media_type'] ?? 'none',
                        'media_caption' => $qData['media_caption'] ?? null,
                        'difficulty'    => $qData['difficulty'] ?? 1,
                        'points'        => $qData['points'] ?? 1,
                        'sort_order'    => $qData['sort_order'] ?? 0,
                        'is_active'     => true,
                    ]);

                    $shuffledOptions = $qData['options'];
                    shuffle($shuffledOptions);

                    foreach ($shuffledOptions as $index => $optData) {
                        QuizOption::query()->create([
                            'question_id' => $question->id,
                            'option_text'  => $optData['option_text'] ?? null,
                            'is_correct'   => (bool) ($optData['is_correct'] ?? false),
                            'sort_order'   => $index,
                        ]);
                    }
                }
            }
        });
    }
}
