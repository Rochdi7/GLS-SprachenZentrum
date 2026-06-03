<?php

return [

    'meta' => [
        'title' => 'Contact | GLS Sprachenzentrum',
    ],

    'hero' => [
        'title' => 'Contactez-nous',
        'subtitle' => 'Notre équipe vous répond rapidement pour toute question sur nos cours, examens et inscriptions.',
    ],

    'locations' => [
        'title' => 'Nos Centres au Maroc',
        'subtitle' => 'Tous nos centres sont idéalement situés au cœur de votre ville. Retrouvez ici les adresses et horaires d’ouverture :',

        'labels' => [
            'address' => 'Adresse',
            'hours'   => 'Horaires',
            'contact' => 'Contact',
        ],

        'buttons' => [
            'maps' => 'Ouvrir sur Google Maps',
        ],

        'global' => [
            'email' => 'info@gls-sprachzentrum.ma',
            'hours' => [
                'Lun - Ven' => '09:30 - 21:30',
                'Sam'       => 'Fermé',
                'Dim'       => 'Fermé',
            ],
        ],

        'list' => [
            [
                'key' => 'casablanca',
                'name' => 'Casablanca',
                'image' => asset('assets/images/sites/casablanca.avif'),
                'address' => '14 Bd de Paris, 1ér étage N8, Casablanca 20000',
                'phone' => '+212 80-8549717',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => '14 Bd de Paris, 1ér étage N8, Casablanca 20000',
            ],
            [
                'key' => 'marrakech',
                'name' => 'Marrakech',
                'image' => asset('assets/images/sites/marrakech.webp'),
                'address' => 'Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech',
                'phone' => '+212 80-86 639 83',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Yacoub El Mansour, Immeuble Espace Guéliz, 3ème étage Bureau 28, Marrakech',
            ],
            [
                'key' => 'rabat',
                'name' => 'Rabat',
                'image' => asset('assets/images/sites/rabat.avif'),
                'address' => 'Avenue Fal Ould Oumeir, Immeuble 77, 1er étage numéro 1, Agdal, Rabat',
                'phone' => '+212 80-85 735 09',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Fal Ould Oumeir, Immeuble 77, 1er étage numéro 1, Agdal, Rabat',
            ],
            [
                'key' => 'kenitra',
                'name' => 'Kénitra',
                'image' => asset('assets/images/sites/kenitra.jpg'),
                'address' => 'Avenue Mohammed V, Bureaux Rania, 7éme étage, Kénitra',
                'phone' => '+212 80-86 514 50',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Avenue Mohammed V, Bureaux Rania, 7éme étage, Kénitra',
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
                'address' => 'Av, Massoude AL Wafkaoui, Places des taxi, Hay Essalam, Agadir',
                'phone' => '+212 606-48 40 51',
                'email' => 'info@gls-sprachzentrum.ma',
                'maps_query' => 'Av Massoude AL Wafkaoui, Places des taxi, Hay Essalam, Agadir',
            ],
        ],
    ],

    'form' => [
        'title' => 'Envoyez-nous un message',
        'subtitle' => 'Remplissez le formulaire et nous vous répondrons dans les plus brefs délais.',
        'name' => 'Nom complet',
        'email' => 'Email',
        'subject' => 'Sujet',
        'message' => 'Message',
        'button' => 'Envoyer',
    ],

];
