<?php

return [

    'meta_title' => 'Learn German in Morocco | German Courses at GLS',
    'meta_description' => 'German language center in Morocco: intensive A1–C1 courses, Goethe exam prep and certifications. Six centers including Rabat and Casablanca.',

    // ========================
    // 🔵 HERO SECTION
    // ========================
    'hero' => [
        'title' => 'Your first step',
        'title2' => 'towards Germany',
        'subtitle_ar' => 'خطوتك الأولى نحو ألمانيا',
        'badge1' => 'Jawohl',
        'badge2' => 'Wunderbar',
        'badge3' => 'Guten Tag',
        'badge4' => 'Freunde',
    ],

    // ========================
    // 🟢 INTRO SECTION
    // ========================
    'site_heading' => [
        'rabat'      => 'German course Rabat – GLS campus',
        'kenitra'    => 'GLS language center Kénitra',
        'marrakech'  => 'German school Marrakech – GLS',
        'sale'       => 'Learn German in Salé – GLS',
        'agadir'     => 'German course Agadir – GLS',
        'casablanca' => 'German school Casablanca – GLS',
    ],

    'intro' => [
        'logo_alt' => 'GLS Sprachenzentrum logo',
        'tagline' => 'Learn German at GLS',
        'heading' => 'Learn. Connect. Discover.',
        'description' => 'Our goal is to support our students through an immersive and motivating language experience.',
        'button' => 'View our courses',
    ],

    'site_image_alt' => [
        'rabat' => 'German course in Rabat – GLS Sprachenzentrum',
        'kenitra' => 'German course in Kénitra – GLS Sprachenzentrum',
        'marrakech' => 'German course in Marrakech – GLS Sprachenzentrum',
        'sale' => 'German course in Salé – GLS Sprachenzentrum',
        'casablanca' => 'German course in Casablanca – GLS Sprachenzentrum',
        'agadir' => 'German course in Agadir – GLS Sprachenzentrum',
    ],

    'video_facade' => [
        'play_aria' => 'Play video: :title',
    ],
    'reviews' => [
        'title' => 'Join Morocco\'s highest-rated German school',
        'rating_line' => '4.9 / 5 (+677 reviews)',

        'items' => [
            [
                'text' => 'GLS helped me a lot to improve my German. The teachers are very patient and passionate.',
                'name' => 'Salma Benyahia',
                'year' => 2025,
            ],
            [
                'text' => 'Ich habe bei GLS einen großartigen Fortschritt gemacht. Die Atmosphäre war freundlich und motivierend.',
                'name' => 'Youssef El Amrani',
                'year' => 2024,
            ],
            [
                'text' => 'The online course is very well structured, I finally understand German grammar!',
                'name' => 'Lina Zahraoui',
                'year' => 2025,
            ],
            [
                'text' => 'I highly recommend GLS to everyone who wants to learn German in a pleasant atmosphere.',
                'name' => 'Rachid El Khattabi',
                'year' => 2024,
            ],
            [
                'text' => 'My teacher was amazing! The classes were fun, interactive and very useful for my exams.',
                'name' => 'Imane Ait Lhaj',
                'year' => 2025,
            ],
            [
                'text' => 'Ich liebe die Energie der Lehrer bei GLS. Sie motivieren jeden Schüler.',
                'name' => 'Hamza Belkadi',
                'year' => 2024,
            ],
            [
                'text' => 'GLS Sprachenzentrum is an amazing place to learn German at your own pace.',
                'name' => 'Nadia Cherkaoui',
                'year' => 2025,
            ],
            [
                'text' => 'The school atmosphere is family-like. Everyone is kind and very professional.',
                'name' => 'Karim Berrada',
                'year' => 2024,
            ],
            [
                'text' => 'I\'m very happy with my GLS courses, the teachers are dynamic and motivating.',
                'name' => 'Hajar Bouziane',
                'year' => 2025,
            ],
            [
                'text' => 'GLS really helped me prepare for my B1 exam. Clear and effective methodology.',
                'name' => 'Ayoub Idrissi',
                'year' => 2025,
            ],
        ],
    ],

    'courses' => [
        'title' => 'Our Courses',

        'intensive' => [
            'title' => 'Intensive German Courses',
            'subtitle' => 'German Courses A1–C1',
            'description' => 'Monday to Friday — 2.5 hours per session',

            'cards' => [
                'a1' => [
                    'letter' => 'A',
                    'number' => '1',
                    'title' => 'German A1<br>Beginner',
                    'text' => 'Learn the basics of German.<br>Perfect for beginners!',
                    'button' => 'View A1 course',
                    'route' => 'front.online-courses',
                ],
                'a2' => [
                    'letter' => 'A',
                    'number' => '2',
                    'title' => 'German A2<br>Elementary',
                    'text' => 'Build a solid foundation in German language.',
                    'button' => 'Explore A2 level',
                    'route' => 'front.exams.gls',
                ],
                'b1' => [
                    'letter' => 'B',
                    'number' => '1',
                    'title' => 'German B1<br>Intermediate',
                    'text' => 'Develop your German language skills.',
                    'button' => 'Goethe prep B1',
                    'route' => 'front.exams.goethe',
                ],
                'b2' => [
                    'letter' => 'B',
                    'number' => '2',
                    'title' => 'German B2<br>Advanced',
                    'text' => 'Reach an advanced level in German with our B2 program.',
                    'button' => 'B2 program',
                    'route' => 'front.online-courses',
                ],
                'c1' => [
                    'letter' => 'C',
                    'number' => '1',
                    'title' => 'German C1<br>Proficient',
                    'text' => 'Master German at an advanced, near-native level with our C1 program.',
                    'button' => 'C1 program',
                    'route' => 'front.niveaux.c1',
                ],
            ],
        ],

        'online' => [
            'title' => 'Online Courses & Exams',
            'subtitle' => 'Preparation, flexibility and certification',

            'cards' => [
                'online' => [
                    'title' => 'Online<br>Courses',
                    'text' => 'Learn German from the comfort of your home.',
                    'button' => 'Online courses',
                    'route' => 'front.online-courses',
                ],
                'gls' => [
                    'title' => 'GLS Exam<br>Preparation',
                    'text' => 'Prepare for official GLS German language exams in Morocco.',
                    'button' => 'View programs',
                    'route' => 'front.exams.gls',
                ],
                'goethe' => [
                    'title' => 'Goethe Exam<br>Preparation',
                    'text' => 'Get Goethe certification recognized internationally.',
                    'button' => 'View programs',
                    'route' => 'front.exams.goethe',
                ],
            ],
        ],
    ],

    'learn_more' => [
        'title' => 'Apprendre l’allemand<br>Avec<br>GLS Maroc',

        'description' => "Chez GLS Maroc, apprendre l’allemand est une expérience immersive et adaptée à chaque étudiant.
    Nos petites classes et nos formateurs certifiés garantissent un accompagnement pédagogique de qualité,
    du niveau débutant jusqu’au niveau avancé, avec un suivi personnalisé pour atteindre vos objectifs en Allemagne.",

        'cards' => [
            [
                'title' => 'Informations<br>tarifs',
                'route' => 'front.pricing',
                'icon' => '
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                     stroke="var(--light--green)" stroke-width="2" viewBox="0 0 24 24">
                     <path d="M4.72787 16.1372C3.18287 14.5912 2.40987 13.8192 2.12287 12.8162C1.83487 11.8132 2.08087 10.7482 2.57287 8.61925L2.85587 7.39125C3.26887 5.59925 3.47587 4.70325 4.08887 4.08925C4.70187 3.47525 5.59887 3.26925 7.39087 2.85625L8.61887 2.57225C10.7489 2.08125 11.8129 1.83525 12.8159 2.12225C13.8189 2.41025 14.5909 3.18325 16.1359 4.72825L17.9659 6.55825C20.6569 9.24825 21.9999 10.5922 21.9999 12.2622C21.9999 13.9332 20.6559 15.2772 17.9669 17.9662C15.2769 20.6562 13.9329 22.0002 12.2619 22.0002C10.5919 22.0002 9.24687 20.6562 6.55787 17.9672L4.72787 16.1372Z"/>
                     <path d="M10.02 10.2892C10.801 9.50816 10.801 8.24183 10.02 7.46079C9.23894 6.67974 7.97261 6.67974 7.19156 7.46079C6.41051 8.24183 6.41051 9.50816 7.19156 10.2892C7.97261 11.0703 9.23894 11.0703 10.02 10.2892Z"/>
                </svg>
            ',
            ],

            [
                'title' => 'Nos<br>groupes',
                'action' => 'open-groups-site-modal',
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
        'title' => 'Welcome to<br>9onsol\'s Talks',

        'description' => "Welcome to <strong>9onsol's Talks</strong> – the podcast that motivates you to take on challenges and reach your goals!<br><br>
            Hosted by <strong>@l9onsol</strong>, each episode brings authentic conversations with students, teachers and inspiring guests from <strong>GLS Morocco</strong> and beyond.<br>
            Listen, learn and let yourself be inspired to grow in your journey – episode after episode.",

        'button' => 'Listen now',
    ],
    'highlights' => [
        'title' => 'Starting Soon!',
        'big_card' => [
            'title' => 'Do What You Musst Course,<br>Pay what you want.',
            'subtitle' => 'A beginner German course you can afford on your own terms',
            'description' => 'Cost shouldn\'t stop you from learning German! Join our full A1 afternoon course on a pay-what-you-want basis. Limited availability, sign up in person in our campus.',
            'start_date' => 'Starting December 2nd (4 Week Duration).',
            'button_directions' => 'Get Directions',
            'button_learn_more' => 'Follow us on Instagram',
        ],
        'card_a1' => [
            'title' => 'New <span class="hh-yellow">A1 Courses</span> Starting&nbsp;Soon!',
            'spots_available' => 'Spots still available!',
            'description' => 'Would you like to start your German Course today?<br>No problem! Register today and get started.',
            'button' => 'Register Now',
        ],
        'card_intensive' => [
            'title' => 'Intensive German Courses in Morocco',
            'join_anytime' => 'Join anytime!',
            'description' => 'Tuesday to Friday, 4 Weeks, 16 hours a week.<br>Learn as long as you like.',
            'button' => 'View Pricelist',
        ],
        'card_c1' => [
            'title' => 'New <span class="hh-yellow">C1 Groups</span> Now Open in Morocco!',
            'badge' => 'Advanced level available!',
            'description' => 'We have opened new C1 groups across our centers in Morocco.<br>Reach a near-native, university-ready level of German with GLS.',
            'button' => 'Discover the C1 course',
        ],
    ],

    'contact' => [
        'title' => 'Any questions?<br>Contact us!',

        'call_label' => 'Call us',
        'email_label' => 'Email us',
        'visit_label' => 'Visit us',
        'follow_label' => 'Follow us',
        'center_label' => 'Choose a center',

        'centers' => [
            'agadir' => 'Agadir',
            'kenitra' => 'Kénitra',
            'casablanca' => 'Casablanca',
            'marrakech' => 'Marrakech',
            'sale' => 'Salé',
            'rabat' => 'Rabat',
            'online' => 'Online',
        ],

        'addresses' => "
            14 Bd de Paris, 1st floor N°8, Casablanca 20000<br>
            3ème étage Bureau 28, Immeuble Espace, Av. Yacoub El Mansour, Marrakech 40000<br>
            Avenue Fal Ould Oumeir, Building 77, 1st floor N°1, Agdal, Rabat<br>
            4ème étage, résidence Nezha, Av. Mohamed V, Kenitra 14000<br>
            Avenue Mohamed V Rue Halima N°12 Diyar, Salé<br>
            2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000
        ",

        'address_by_city' => [
            'casablanca' => '14 Bd de Paris, 1st floor N°8, Casablanca 20000',
            'marrakech'  => '3ème étage Bureau 28, Immeuble Espace, Av. Yacoub El Mansour, Marrakech 40000',
            'rabat'      => 'Avenue Fal Ould Oumeir, Building 77, 1st floor N°1, Agdal, Rabat',
            'kenitra'    => '4ème étage, résidence Nezha, Av. Mohamed V, Kenitra 14000',
            'sale'       => 'Avenue Mohamed V Rue Halima N°12 Diyar, Salé',
            'agadir'     => '2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000',
        ],

        'maps_url_by_city' => [
            'casablanca' => 'https://maps.app.goo.gl/EdqBoa3KWEYjuzoq7',
            'marrakech'  => 'https://maps.app.goo.gl/krR8pGZue3DW3yyv6',
            'rabat'      => 'https://maps.app.goo.gl/mUnSAVYEnGToS8i2A',
            'kenitra'    => 'https://maps.app.goo.gl/pEsso9L8ygWpdSor5',
            'sale'       => 'https://maps.app.goo.gl/pbSW4y4tt9RThx4a7',
            'agadir'     => 'https://maps.app.goo.gl/VX48ZDGFyXCyxsGU7',
        ],

        // Multi-branch list. Each city is an array of { address, maps_url }.
        // Used to render the "Visit us" links in the contact card.
        'branch_label' => 'branch',

        'centers_list' => [
            'casablanca' => [
                ['address' => '14 Bd de Paris, 1st floor N°8, Casablanca 20000', 'maps_url' => 'https://maps.app.goo.gl/EdqBoa3KWEYjuzoq7', 'phones' => [['n' => '+212 808 549717', 't' => 'call'], ['n' => '+212 651 447651', 't' => 'whatsapp']]],
            ],
            'marrakech' => [
                ['address' => '3ème étage Bureau 28, Immeuble Espace, Av. Yacoub El Mansour, Marrakech 40000', 'maps_url' => 'https://maps.app.goo.gl/krR8pGZue3DW3yyv6', 'phones' => [['n' => '+212 669 515019', 't' => 'call'], ['n' => '+212 603 160277', 't' => 'whatsapp']]],
            ],
            'rabat' => [
                ['address' => 'Avenue Fal Ould Oumeir, Building 77, 1st floor N°1, Rabat 10000', 'maps_url' => 'https://maps.app.goo.gl/mUnSAVYEnGToS8i2A', 'phones' => [['n' => '+212 808 573509', 't' => 'call'], ['n' => '+212 537 671420', 't' => 'call'], ['n' => '+212 660 820082', 't' => 'whatsapp']]],
                ['address' => '1st floor, 99 Av. Fal Ould Oumeir, Rabat 10000',                'maps_url' => 'https://maps.app.goo.gl/dnXUTeupDvomfjg89'],
            ],
            'kenitra' => [
                ['address' => '4ème étage, résidence Nezha, Av. Mohamed V, Kenitra 14000', 'maps_url' => 'https://maps.app.goo.gl/pEsso9L8ygWpdSor5', 'phones' => [['n' => '+212 808 651450', 't' => 'call'], ['n' => '+212 628 273803', 't' => 'whatsapp'], ['n' => '+212 669 515019', 't' => 'whatsapp']]],
            ],
            'sale' => [
                ['address' => 'Avenue Mohamed V Rue Halima N°12 Diyar, Salé', 'maps_url' => 'https://maps.app.goo.gl/pbSW4y4tt9RThx4a7', 'phones' => [['n' => '+212 808 540625', 't' => 'call'], ['n' => '+212 622 996078', 't' => 'whatsapp']]],
            ],
            'agadir' => [
                ['address' => '2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000', 'maps_url' => 'https://maps.app.goo.gl/VX48ZDGFyXCyxsGU7', 'phones' => [['n' => '+212 528 228430', 't' => 'call'], ['n' => '+212 606 484051', 't' => 'whatsapp']]],
            ],
            'online' => [
                ['phones' => [['n' => '+212 752 070838', 't' => 'both']]],
            ],
        ],
    ],

    'site_modal' => [
        'kicker' => 'Our centers',
        'title' => 'Choose your center',
        'marrakech' => 'Marrakech',
        'casablanca' => 'Casablanca',
        'rabat' => 'Rabat',
        'kenitra' => 'Kénitra',
        'sale' => 'Salé',
        'agadir' => 'Agadir',
    ],

    'groups' => [
        'empty_active' => 'No active groups',
        'empty_upcoming' => 'No upcoming groups planned',
    ],

    // ========================
    // MARKETING VIDEOS SECTION
    // ========================
    'marketing_videos' => [
        'title' => 'OUR VIDEOS',
        'subtitle' => 'Discover GLS Sprachenzentrum in video',
    ],

    // ========================
    // TESTIMONIALS VIDEOS SECTION
    // ========================
    'testimonials_videos' => [
        'title' => 'VIDEO TESTIMONIALS',
        'subtitle' => 'Discover our students\' stories',
        'aria_section' => 'Video testimonials',
        'aria_preview' => 'Testimonials preview',
        'aria_play' => 'Play video',
        'aria_prev' => 'Previous',
        'aria_next' => 'Next',
        'aria_modal' => 'Video playback',
        'aria_close' => 'Close',
        'aria_card' => 'View testimonial from',
        'items' => [
            [
                'name' => 'Alex',
                'age' => 14,
                'role' => 'GLS Student',
                'group' => 'Level B1 – Intermediate',
                'vimeo' => '1172183086',
            ],
            [
                'name' => 'Kate',
                'age' => null,
                'role' => 'Oleg\'s mother (15 years old)',
                'group' => 'Level B1 – Intermediate',
                'vimeo' => '1172183039',
            ],
            [
                'name' => 'Jay',
                'age' => 13,
                'role' => 'GLS Student',
                'group' => 'Level B1 – Intermediate',
                'vimeo' => '1172182987',
            ],
            [
                'name' => 'Sara',
                'age' => 15,
                'role' => 'GLS Student',
                'group' => 'Level B1 – Intermediate',
                'vimeo' => '1172182943',
            ],
            [
                'name' => 'Oleg',
                'age' => 15,
                'role' => 'GLS Student',
                'group' => 'Level B1 – Intermediate',
                'vimeo' => '1172182895',
            ],
        ],
    ],

    'partners' => [
        'title' => 'Our Cooperation Partners',
        'aria_label' => 'Our Cooperation Partners',
    ],

    'student_services' => [
        'eyebrow'      => 'Studentenakte · Student space',
        'title_lead'   => 'Your',
        'title_accent' => 'GLS file',
        'title_tail'   => ', within reach.',

        'meta_label'   => 'Official portal',
        'meta_text'    => 'Verifications, requests and tracking — handled directly from your reference number.',

        'feature_tag'  => 'Featured service',
        'feature_title'=> 'Track your Morocco – Germany translations',
        'feature_desc' => 'From drop-off at our office to final delivery, follow your documents in real time with your CIN.',
        'feature_cta'  => 'Track my documents',

        'preview_order'   => 'Order #2041',
        'preview_pages'   => '3 documents · 5 pages',
        'preview_status'  => 'With Translator',
        'preview_step1'   => 'Received',
        'preview_step2'   => 'Translation',
        'preview_step3'   => 'Delivered',

        'item1_title'  => 'Verify a GLS certificate',
        'item1_desc'   => 'Instantly authenticate a certificate issued by GLS Sprachenzentrum using its unique number.',

        'item2_title'  => 'Request a participation attestation',
        'item2_desc'   => 'Submit your request online — our team will email it to you once validated.',

        'foot_text'    => 'Service reserved for current and former GLS students.',

        'tr_title_short'        => 'Translation tracking',
        'tr_desc_short'         => 'Track your Morocco – Germany translations with your CIN, from drop-off to delivery.',

        'cert_tag'              => 'Instant check',
        'cert_cta'              => 'Verify a certificate',
        'cert_preview_label'    => 'Number entered',
        'cert_preview_status'   => 'Authentic',
        'cert_preview_level'    => 'Level',
        'cert_preview_authentic'=> 'Issued by GLS Sprachenzentrum',

        'att_tag'               => 'Online request',
        'att_cta'               => 'Request my attestation',
        'att_preview_request'   => 'Request sent',
        'att_preview_status'    => 'Under review',
        'att_preview_level'     => 'Level',
        'att_step1'             => 'Submitted',
        'att_step2'             => 'Validation',
        'att_step3'             => 'Email',
    ],
];
