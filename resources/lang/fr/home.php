<?php

return [
    // ========================
    // 🔵 HERO SECTION
    // ========================
    'hero' => [
        'title' => 'Apprenez l’allemand au Maroc avec GLS',
        'badge1' => 'Jawohl',
        'badge2' => 'Wunderbar',
        'badge3' => 'Guten Tag',
        'badge4' => 'Freunde',
    ],

    // ========================
    // 🟢 INTRO SECTION
    // ========================
    'intro' => [
        'tagline' => 'Apprenez l’allemand chez GLS',
        'heading' => 'Apprendre. Connecter. Découvrir.',
        'description' => 'Notre objectif est d’accompagner nos étudiants à travers une expérience linguistique immersive et motivante.',
        'button' => 'Voir nos cours',
    ],
    'reviews' => [
        'title' => 'Rejoignez l’école d’allemand la mieux notée au Maroc',
        'rating_line' => '4.9 / 5 (+677 avis)',

        'items' => [
            [
                'text' => 'GLS m’a beaucoup aidé à améliorer mon allemand. Les professeurs sont très patients et passionnés.',
                'name' => 'Salma Benyahia',
                'year' => 2025,
            ],
            [
                'text' => 'Ich habe bei GLS einen großartigen Fortschritt gemacht. Die Atmosphäre war freundlich et motivierend.',
                'name' => 'Youssef El Amrani',
                'year' => 2024,
            ],
            [
                'text' => 'Le cours en ligne est super bien structuré, je comprends enfin la grammaire allemande !',
                'name' => 'Lina Zahraoui',
                'year' => 2025,
            ],
            [
                'text' => 'Je recommande vivement GLS à tous ceux qui veulent apprendre l’allemand dans une ambiance agréable.',
                'name' => 'Rachid El Khattabi',
                'year' => 2024,
            ],
            [
                'text' => 'Mon professeur était incroyable ! Les cours étaient amusants, interactifs et très utiles pour mes examens.',
                'name' => 'Imane Ait Lhaj',
                'year' => 2025,
            ],
            [
                'text' => 'Ich liebe die Energie der Lehrer bei GLS. Sie motivieren jeden Schüler.',
                'name' => 'Hamza Belkadi',
                'year' => 2024,
            ],
            [
                'text' => 'GLS Sprachenzentrum est un endroit incroyable pour apprendre l’allemand à ton rythme.',
                'name' => 'Nadia Cherkaoui',
                'year' => 2025,
            ],
            [
                'text' => 'L’ambiance de l’école est familiale. Tout le monde est gentil et très professionnel.',
                'name' => 'Karim Berrada',
                'year' => 2024,
            ],
            [
                'text' => 'Je suis très contente de mes cours à GLS, les professeurs sont dynamiques et motivants.',
                'name' => 'Hajar Bouziane',
                'year' => 2025,
            ],
            [
                'text' => 'GLS m’a vraiment aidé à préparer mon examen B1. Méthodologie claire et efficace.',
                'name' => 'Ayoub Idrissi',
                'year' => 2025,
            ],
        ],
    ],

    'courses' => [
        'title' => 'Nos cours',

        'intensive' => [
            'title' => 'Cours intensifs d’allemand',
            'subtitle' => 'Cours d’allemand A1 – B2',
            'description' => 'Du lundi au vendredi — 2h30 par séance.',

            'levels' => [
                [
                    'letter' => 'A',
                    'number' => '1',
                    'title' => 'Apprendre<br>l’Allemand A1',
                    'text' => 'Apprenez les bases de l’allemand. Parfait pour débuter !',
                    'color' => '',
                ],
                [
                    'letter' => 'A',
                    'number' => '2',
                    'title' => 'Apprendre<br>l’Allemand A2',
                    'text' => 'Construisez une base solide en allemand.',
                    'color' => 'is-green',
                ],
                [
                    'letter' => 'B',
                    'number' => '1',
                    'title' => 'Apprendre<br>l’Allemand B1',
                    'text' => 'Développez vos compétences en allemand.',
                    'color' => 'is-purple',
                ],
                [
                    'letter' => 'B',
                    'number' => '2',
                    'title' => 'Apprendre<br>l’Allemand B2',
                    'text' => 'Atteignez un niveau avancé en allemand.',
                    'color' => 'is-yellow',
                ],
            ],
        ],

        'online' => [
            'title' => 'Cours en ligne & Examens',
            'subtitle' => 'Préparation, flexibilité et certification.',

            'items' => [
                [
                    'title' => 'Cours<br>En Ligne',
                    'text' => 'Apprenez l’allemand depuis chez vous !',
                    'button' => 'En savoir plus',
                    'color' => 'is-orange',
                ],
                [
                    'title' => 'Préparation<br>Examens GLS',
                    'text' => 'Préparez-vous aux examens officiels GLS au Maroc.',
                    'button' => 'Voir les programmes',
                    'color' => 'is-green',
                ],
                [
                    'title' => 'Préparation<br>Examens Goethe',
                    'text' => 'Obtenez la certification internationale Goethe.',
                    'button' => 'Voir les programmes',
                    'color' => 'is-purple',
                ],
            ],
        ],

        'buttons' => [
            'learn_more' => 'En savoir plus',
        ],
    ],
    'learn_more' => [
        'title' => 'Apprendre l’allemand<br>Avec<br>GLS Maroc',

        'description' => "Chez GLS Maroc, apprendre l’allemand est une expérience immersive et adaptée à chaque étudiant.
        Nos petites classes et nos formateurs certifiés garantissent un accompagnement pédagogique de qualité,
        du niveau débutant jusqu’au niveau avancé, avec un suivi personnalisé pour atteindre vos objectifs en Allemagne.",

        'cards' => [
            // Carte 1 — Informations des tarifs
            [
                'title' => 'Informations<br>tarifs',
                'link' => route('front.pricing'),
                'icon' => '
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                     stroke="var(--light--green)" stroke-width="2" viewBox="0 0 24 24">
                     <path d="M4.72787 16.1372C3.18287 14.5912 2.40987 13.8192 2.12287 12.8162C1.83487 11.8132 2.08087 10.7482 2.57287 8.61925L2.85587 7.39125C3.26887 5.59925 3.47587 4.70325 4.08887 4.08925C4.70187 3.47525 5.59887 3.26925 7.39087 2.85625L8.61887 2.57225C10.7489 2.08125 11.8129 1.83525 12.8159 2.12225C13.8189 2.41025 14.5909 3.18325 16.1359 4.72825L17.9659 6.55825C20.6569 9.24825 21.9999 10.5922 21.9999 12.2622C21.9999 13.9332 20.6559 15.2772 17.9669 17.9662C15.2769 20.6562 13.9329 22.0002 12.2619 22.0002C10.5919 22.0002 9.24687 20.6562 6.55787 17.9672L4.72787 16.1372Z"/>
                     <path d="M10.02 10.2892C10.801 9.50816 10.801 8.24183 10.02 7.46079C9.23894 6.67974 7.97261 6.67974 7.19156 7.46079C6.41051 8.24183 6.41051 9.50816 7.19156 10.2892C7.97261 11.0703 9.23894 11.0703 10.02 10.2892Z"/>
                </svg>
            ',
            ],

            // Carte 2 — Nos groupes d'études
            [
                'title' => 'Nos<br>groupes',
                'link' => route('front.about'),
                'icon' => '
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                     stroke="var(--light--green)" stroke-width="2" viewBox="0 0 24 24">
                     <path d="M2 12C2 8.229 2 6.343 3.172 5.172C4.344 4.001 6.229 4 10 4H14C17.771 4 19.657 4 20.828 5.172C21.999 6.344 22 8.229 22 12V14C22 17.771 22 19.657 20.828 20.828C19.656 21.999 17.771 22 14 22H10C6.229 22 4.343 22 3.172 20.828C2.001 19.656 2 17.771 2 14V12Z"/>
                     <path d="M7 4V2.5M17 4V2.5M2.5 9H21.5" stroke-linecap="round"/>
                </svg>
            ',
            ],
        ],
    ],

    '9onsol' => [
        'title' => 'Willkommen to<br>9onsol’s Talks',

        'description' => "Willkommen dans <strong>9onsol’s Talks</strong> – le podcast qui stimule votre motivation pour
            relever des défis et atteindre vos objectifs !<br><br>
            Animé par <strong>@l9onsol</strong>, chaque épisode apporte des conversations authentiques avec
            des étudiants, des professeurs et des invités inspirants de <strong>GLS Morocco</strong> et d’ailleurs.<br>
            Écoutez, apprenez et laissez-vous inspirer pour évoluer dans votre parcours – épisode après épisode.",

        'button' => 'Écouter maintenant',
    ],
    'contact' => [
        'title' => 'Des questions ?<br>Contactez-nous !',

        'call_label' => 'Appelez-nous',
        'email_label' => 'Écrivez-nous',
        'visit_label' => 'Visitez-nous',
        'follow_label' => 'Suivez-nous',

        'addresses' => "
            14 Bd de Paris, 1er étage N°8, Casablanca 20000<br>
            Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech<br>
            Avenue Fal Ould Oumeir, Immeuble 77, 1er étage N°1, Agdal, Rabat<br>
            Avenue Mohammed V, Bureaux Rania, 7ème étage, Kénitra<br>
            Avenue Mohamed V Rue Halima N°12 Diyar, Salé<br>
            Av. Massoude Al Wafkaoui, Place des taxis, Hay Essalam, Agadir
        ",
    ],
];
