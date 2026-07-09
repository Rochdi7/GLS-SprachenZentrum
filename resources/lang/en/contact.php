<?php

return [

    'meta' => [
        'title' => 'Contact | GLS Sprachenzentrum',
    ],

    'hero' => [
        'title' => 'Contact us',
        'subtitle' => 'Our team responds quickly to any questions about our courses, exams and registrations.',
    ],

    'locations' => [
        'title' => 'Our Centers in Morocco',
        'subtitle' => 'All our centers are ideally located in the heart of your city. Find addresses and opening hours here:',

        'labels' => [
            'address' => 'Address',
            'hours'   => 'Hours',
            'contact' => 'Contact',
        ],

        'buttons' => [
            'maps' => 'Open on Google Maps',
        ],

        'global' => [
            'email' => 'info@gls-sprachzentrum.ma',
            'hours' => [
                'Mon - Fri' => '09:30 - 21:30',
                'Sat'       => 'Closed',
                'Sun'       => 'Closed',
            ],
        ],

        'list' => [
            [
                'key' => 'casablanca',
                'name' => 'Casablanca',
                'image' => asset('assets/images/sites/casablanca.avif'),
                'address' => '14 Bd de Paris, 1st floor N8, Casablanca 20000',
                'phone' => '+212 80-8549717',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '14 Bd de Paris, 1st floor N8, Casablanca 20000',
            ],
            [
                'key' => 'marrakech',
                'name' => 'Marrakech',
                'image' => asset('assets/images/sites/marrakech.webp'),
                'address' => '3ème étage Bureau 28, Immeuble Espace, Av. Yacoub El Mansour, Marrakech 40000',
                'phone' => '+212 80-86 639 83',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '3ème étage Bureau 28, Immeuble Espace, Av. Yacoub El Mansour, Marrakech 40000',
            ],
            [
                'key' => 'rabat',
                'name' => 'Rabat',
                'image' => asset('assets/images/sites/rabat.avif'),
                'address' => 'Avenue Fal Ould Oumeir, Building 77, 1st floor number 1, Agdal, Rabat',
                'phone' => '+212 80-85 735 09',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Fal Ould Oumeir, Building 77, 1st floor number 1, Agdal, Rabat',
            ],
            [
                'key' => 'kenitra',
                'name' => 'Kénitra',
                'image' => asset('assets/images/sites/kenitra.jpg'),
                'address' => '4ème étage, résidence Nezha, Av. Mohamed V, Kenitra 14000',
                'phone' => '+212 80-86 514 50',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '4ème étage, résidence Nezha, Av. Mohamed V, Kenitra 14000',
            ],
            [
                'key' => 'sale',
                'name' => 'Salé',
                'image' => asset('assets/images/sites/sale.avif'),
                'address' => 'Avenue Mohamed V, Rue Halima N12 Diyar, Salé',
                'phone' => '+212 80-85 40 625',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Mohamed V, Rue Halima N12 Diyar, Salé',
            ],
            [
                'key' => 'agadir',
                'name' => 'Agadir',
                'image' => asset('assets/images/sites/agadir.avif'),
                'address' => '2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000',
                'phone' => '+212 606-48 40 51',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '2ème étage, Av. Massoude Al Wafkaoui, Agadir 80000',
            ],
        ],
    ],

    'form' => [
        'title' => 'Send us a message',
        'subtitle' => 'Fill out the form and we\'ll get back to you as soon as possible.',
        'name' => 'Full name',
        'email' => 'Email',
        'subject' => 'Subject',
        'message' => 'Message',
        'button' => 'Send',
    ],

];
