<?php

return [

    'meta' => [
        'title' => 'Kontakt | GLS Sprachenzentrum',
    ],

    'hero' => [
        'title' => 'Kontaktieren Sie uns',
        'subtitle' => 'Unser Team antwortet schnell auf alle Fragen zu unseren Kursen, Prüfungen und Anmeldungen.',
    ],

    'locations' => [
        'title' => 'Unsere Zentren in Marokko',
        'subtitle' => 'Alle unsere Zentren befinden sich ideal im Herzen Ihrer Stadt. Hier finden Sie Adressen und Öffnungszeiten:',
        'labels' => [
            'address' => 'Adresse',
            'hours'   => 'Öffnungszeiten',
            'contact' => 'Kontakt',
        ],
        'buttons' => [
            'maps' => 'Auf Google Maps öffnen',
        ],
        'global' => [
            'email' => 'info@gls-sprachzentrum.ma',
            'hours' => [
                'Mo - Fr' => '09:30 - 21:30',
                'Sa'      => 'Geschlossen',
                'So'      => 'Geschlossen',
            ],
        ],
        'list' => [
            [
                'key' => 'casablanca',
                'name' => 'Casablanca',
                'image' => asset('assets/images/sites/casablanca.jpg'),
                'address' => '14 Bd de Paris, 1. Stock N8, Casablanca 20000',
                'phone' => '+212 80-8549717',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '14 Bd de Paris, 1st floor N8, Casablanca 20000',
            ],
            [
                'key' => 'marrakech',
                'name' => 'Marrakech',
                'image' => asset('assets/images/sites/marrakech.webp'),
                'address' => 'Avenue Yacoub El Mansour, Espace Guéliz Gebäude, 3. Stock Büro 28, Marrakech',
                'phone' => '+212 80-86 639 83',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Yacoub El Mansour, Espace Guéliz Building, 3rd floor Office 28, Marrakech',
            ],
            [
                'key' => 'rabat',
                'name' => 'Rabat',
                'image' => asset('assets/images/sites/rabat.jpg'),
                'address' => 'Avenue Fal Ould Oumeir, Gebäude 77, 1. Stock Nr. 1, Agdal, Rabat',
                'phone' => '+212 80-85 735 09',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Fal Ould Oumeir, Building 77, 1st floor number 1, Agdal, Rabat',
            ],
            [
                'key' => 'kenitra',
                'name' => 'Kénitra',
                'image' => asset('assets/images/sites/kenitra.jpg'),
                'address' => 'Avenue Mohammed V, Rania Büros, 7. Stock, Kénitra',
                'phone' => '+212 80-86 514 50',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Mohammed V, Rania Offices, 7th floor, Kénitra',
            ],
            [
                'key' => 'sale',
                'name' => 'Salé',
                'image' => asset('assets/images/sites/sale.webp'),
                'address' => 'Avenue Mohamed V, Rue Halima N12 Diyar, Salé',
                'phone' => '+212 80-85 40 625',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Mohamed V, Rue Halima N12 Diyar, Salé',
            ],
            [
                'key' => 'agadir',
                'name' => 'Agadir',
                'image' => asset('assets/images/sites/agadir.avif'),
                'address' => 'Av. Massoude AL Wafkaoui, Places des taxi, Hay Essalam, Agadir',
                'phone' => '+212 606-48 40 51',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Av. Massoude AL Wafkaoui, Places des taxi, Hay Essalam, Agadir',
            ],
        ],
    ],

    'form' => [
        'title' => 'Senden Sie uns eine Nachricht',
        'subtitle' => 'Füllen Sie das Formular aus und wir melden uns so schnell wie möglich bei Ihnen.',
        'name' => 'Vollständiger Name',
        'email' => 'E-Mail',
        'subject' => 'Betreff',
        'message' => 'Nachricht',
        'button' => 'Senden',
    ],
];
