<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Canonical host (non-www preferred; matches sitemap APP_URL)
    |--------------------------------------------------------------------------
    */
    'canonical_host' => env('SEO_CANONICAL_HOST', parse_url(env('APP_URL', 'https://gls-sprachzentrum.ma'), PHP_URL_HOST)),

    'force_https' => env('SEO_FORCE_HTTPS', true),

    /*
    |--------------------------------------------------------------------------
    | Default Open Graph image
    |--------------------------------------------------------------------------
    */
    'og_image' => env('SEO_OG_IMAGE', '/assets/images/IMG_4399.avif'),

    /*
    |--------------------------------------------------------------------------
    | Locale fallbacks when a page does not define @section('title')
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'fr' => [
            'title' => 'GLS Sprachenzentrum – Apprendre l’allemand au Maroc',
            'description' => 'Centre de langue allemande au Maroc : cours intensifs A1–C1, préparation Goethe et certifications. 6 centres au Maroc.',
        ],
        'en' => [
            'title' => 'GLS Sprachenzentrum – Learn German in Morocco',
            'description' => 'German language center in Morocco: intensive A1–C1 courses, Goethe exam prep and certifications. Six centers nationwide.',
        ],
        'de' => [
            'title' => 'Deutsch lernen in Marokko | Deutschkurs & Sprachzentrum GLS',
            'description' => 'Deutschschule und Sprachzentrum in Marokko: Deutschkurse A1–C1, Goethe-Vorbereitung, Zertifikate und Studienweg nach Deutschland. 6 Standorte.',
        ],
        'ar' => [
            'title' => 'GLS Sprachenzentrum – تعلم الألمانية في المغرب',
            'description' => 'مركز تعليم اللغة الألمانية في المغرب: دورات مكثفة A1–C1، تحضير امتحان غوته وشهادات معتمدة. 6 فروع.',
        ],
    ],

    'twitter' => [
        'site' => env('SEO_TWITTER_SITE', '@glssprachenzentrum'),
    ],

];
