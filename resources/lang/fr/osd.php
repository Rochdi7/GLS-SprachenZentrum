<?php

return [
    'meta' => [
        'title' => 'Examen ÖSD – GLS Sprachenzentrum Maroc',
    ],

    'hero' => [
        'subtitle' => 'Examen ÖSD au Maroc',
        'title' => 'Certification Allemande ÖSD chez GLS',
        'alt' => 'Examen ÖSD GLS Maroc',
    ],

    'intro' => [
        'p1' => 'Vous vous demandez à quel point votre allemand est solide ? Comment vous exprimez, comprenez et utilisez la langue dans des situations quotidiennes ? Ce sont des questions fréquentes pour les apprenants qui préparent leurs études, leur travail ou un dossier de visa. C’est exactement là que l’examen <strong>ÖSD</strong> devient important.',
        'p2' => 'Au <strong>GLS Sprachenzentrum Maroc</strong>, vous pouvez vous préparer aux examens officiels ÖSD et déterminer clairement votre niveau d’allemand actuel. Nos entraînements, examens blancs et séances ciblées vous aident à comprendre vos points forts et ce qu’il faut améliorer.',
        'p3' => 'Quel examen ÖSD vous correspond ? Comment se déroule l’examen ? Et comment bien vous préparer ? Vous trouverez ici des explications simples et claires sur les niveaux A1, A2, B1 et B2 — ainsi que notre parcours recommandé avec GLS.',
        'p4' => 'Si vous souhaitez passer un examen ÖSD avec GLS, vous êtes au bon endroit !',
    ],

    'path' => [
        'title' => 'Votre parcours des cours GLS à l’examen ÖSD',

        'card1' => [
            'title' => 'Terminez<br>votre niveau',
            'text' => 'Chaque étudiant valide son niveau (A1–B2) avec une formation structurée, des exercices et une évaluation interne.',
            'button' => 'Voir les cours',
            'route' => 'front.intensive-courses', // ✅ dynamique
        ],

        'card2' => [
            'title' => 'Préparation<br>interne',
            'text' => 'Après chaque niveau, une préparation ciblée au format ÖSD est organisée pour garantir votre réussite.',
            'button' => 'Préparation ÖSD',
            'route' => 'front.exams.osd', // ✅ dynamique (ou mets une page "prep" si tu en as)
        ],

        'card3' => [
            'title' => 'Programmation<br>des examens',
            'text' => 'Une fois prêt, GLS programme la date de votre examen officiel directement avec le centre ÖSD.',
            'button' => 'Voir les dates',
            'route' => 'front.contact', // ✅ dynamique (si pas de page dates)
        ],

        'card4' => [
            'title' => 'Examen officiel<br>ÖSD',
            'text' => 'Vous passez votre examen ÖSD et recevez un certificat reconnu internationalement.',
            'button' => "Passer l'examen",
            'route' => 'front.contact', // ✅ dynamique (inscription examen via contact)
        ],
    ],

    'levels' => [
        'title1' => 'Votre chemin des examens GLS au certificat ÖSD',
        'text1' => 'Chaque étudiant suit un parcours clair. Après avoir terminé un niveau (A1–B2), vous passez un <strong>examen interne GLS</strong> avec votre professeur.',
        'text2' => 'Une fois votre niveau validé, GLS inscrit votre nom à l’examen officiel ÖSD correspondant.',

        'title2' => 'Ce que vous devez réussir avant l’examen ÖSD',

        'a1_listen' => 'A1 – Compréhension orale',
        'a1_listen_text' => 'Vous montrez que vous pouvez comprendre des conversations simples, des annonces et des situations quotidiennes.',

        'a1_grammar' => 'A1 – Grammaire',
        'a1_grammar_text' => 'Cette partie vérifie votre maîtrise des articles, de la structure des phrases et des formes de base.',

        'a1_read' => 'A1 – Compréhension écrite',
        'a1_read_text' => 'Vous lisez de courts textes et trouvez des informations clés, utile pour messages simples et emails.',

        'a1_write' => 'A1 – Expression écrite',
        'a1_write_text' => 'Vous répondez à un court message ou une situation quotidienne, preuve de communication écrite basique.',

        'title3' => "Après avoir réussi l'examen de niveau",
        'gls_to_osd' => 'Du test GLS → Certificat ÖSD',
        'gls_to_osd_text1' => 'Une fois votre examen interne validé, nous vous guidons vers l’examen officiel ÖSD.',
        'gls_to_osd_text2' => 'Le certificat ÖSD final est reconnu pour les études, le travail, l’Ausbildung ou les demandes de visa.',
    ],
    'exams' => [
        'title' => 'Tous les examens en interne !',
        'subtitle' => 'Centre officiel d’examen ÖSD & Examen GLS (bientôt)',

        'card1' => [
            'title' => 'Examen ÖSD',
            'text' => 'Examen officiel autrichien reconnu pour les études, le travail, l’Ausbildung et les visas.',
            'button' => 'En savoir plus',
            'route' => 'front.exams.osd', 
        ],

        'card2' => [
            'title' => 'Examen GLS',
            'text' => 'Un nouvel examen moderne développé par GLS pour une validation rapide et claire du niveau.',
            'button' => 'Bientôt disponible',
            'route' => 'front.exams.gls', 
        ],

        'card3' => [
            'title' => 'Test de positionnement',
            'text' => 'Vous ne connaissez pas votre niveau ? Notre test vous guide vers la préparation ÖSD adaptée.',
            'button' => 'Commencer le test',
            'route' => 'front.discover-your-level', 
        ],
    ],

    'contact' => [
        'title' => 'Des Questions ? Contactez-nous !',

        'call' => 'APPELEZ-NOUS',
        'email' => 'ÉCRIVEZ-NOUS',
        'visit' => 'RENDEZ-NOUS VISITE',
        'follow' => 'SUIVEZ-NOUS',

        'addresses' => '
            14 Bd de Paris, 1er étage N°8, Casablanca 20000<br>
            Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech<br>
            Avenue Fal Ould Oumeir, Immeuble 77, 1er étage N°1, Agdal, Rabat<br>
            Avenue Mohammed V, Bureaux Rania, 7ème étage, Kénitra<br>
            Avenue Mohamed V Rue Halima N°12 Diyar, Salé<br>
            2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000
        ',

        'map_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3307.8001465016737!2d-6.8485901!3d33.9976668!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76dcf7a656da5%3A0xcaf46ae5e6e81d87!2sGLS%20Sprachenzentrum%20-%20Centre%20GLS%20de%20langue%20Allemande%20Rabat!5e0!3m2!1sen!2sma!4v1768563248691!5m2!1sen!2sma" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade',
    ],
];
