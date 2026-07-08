<?php

return [

    'meta' => [
        'title' => 'اتصل بنا | GLS Sprachenzentrum',
    ],

    'hero' => [
        'title' => 'اتصل بنا',
        'subtitle' => 'فريقنا يجيب بسرعة على جميع أسئلتكم حول الدورات والامتحانات والتسجيل.',
    ],

    'locations' => [
        'title' => 'مراكزنا في المغرب',
        'subtitle' => 'جميع مراكزنا تقع في موقع مثالي في قلب مدينتك. اكتشف هنا العناوين وأوقات العمل:',

        'labels' => [
            'address' => 'العنوان',
            'hours'   => 'أوقات العمل',
            'contact' => 'الاتصال',
        ],

        'buttons' => [
            'maps' => 'فتح على خرائط جوجل',
        ],

        'global' => [
            'email' => 'info@gls-sprachzentrum.ma',
            'hours' => [
                'الإثنين - الجمعة' => '09:30 - 21:30',
                'السبت'       => 'مغلق',
                'الأحد'       => 'مغلق',
            ],
        ],

        'list' => [
            [
                'key' => 'casablanca',
                'name' => 'الدار البيضاء',
                'image' => asset('assets/images/sites/casablanca.avif'),
                'address' => '14 Bd de Paris, 1ér étage N8, Casablanca 20000',
                'phone' => '+212 80-8549717',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '14 Bd de Paris, 1ér étage N8, Casablanca 20000',
            ],
            [
                'key' => 'marrakech',
                'name' => 'مراكش',
                'image' => asset('assets/images/sites/marrakech.webp'),
                'address' => 'Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech',
                'phone' => '+212 80-86 639 83',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech',
            ],
            [
                'key' => 'rabat',
                'name' => 'الرباط',
                'image' => asset('assets/images/sites/rabat.avif'),
                'address' => 'Avenue Fal Ould Oumeir, Immeuble 77, 1er étage numéro 1, Agdal, Rabat',
                'phone' => '+212 80-85 735 09',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Fal Ould Oumeir, Immeuble 77, 1er étage numéro 1, Agdal, Rabat',
            ],
            [
                'key' => 'kenitra',
                'name' => 'القنيطرة',
                'image' => asset('assets/images/sites/kenitra.jpg'),
                'address' => 'Avenue Mohammed V, Bureaux Rania, 7éme étage, Kénitra',
                'phone' => '+212 80-86 514 50',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Mohammed V, Bureaux Rania, 7éme étage, Kénitra',
            ],
            [
                'key' => 'sale',
                'name' => 'سلا',
                'image' => asset('assets/images/sites/sale.avif'),
                'address' => 'Avenue Mohamed V, Rue Halima N12 Diyar, Salé',
                'phone' => '+212 80-85 40 625',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Mohamed V, Rue Halima N12 Diyar, Salé',
            ],
            [
                'key' => 'agadir',
                'name' => 'أكادير',
                'image' => asset('assets/images/sites/agadir.avif'),
                'address' => '2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000',
                'phone' => '+212 606-48 40 51',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000',
            ],
        ],
    ],

    'form' => [
        'title' => 'أرسل لنا رسالة',
        'subtitle' => 'املأ الاستمارة وسنرد عليك في أقرب وقت ممكن.',
        'name' => 'الاسم الكامل',
        'email' => 'البريد الإلكتروني',
        'subject' => 'الموضوع',
        'message' => 'الرسالة',
        'button' => 'إرسال',
    ],

];
