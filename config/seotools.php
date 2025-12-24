<?php
/**
 * https://github.com/artesaos/seotools
 * Optimized configuration for GLS Sprachen Zentrum
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Inertia Support
    |--------------------------------------------------------------------------
    */
    'inertia' => env('SEO_TOOLS_INERTIA', false),

    /*
    |--------------------------------------------------------------------------
    | META TAGS
    |--------------------------------------------------------------------------
    */
    'meta' => [

        'defaults' => [
            // Default title (false = force dynamic per page)
            'title'       => false,

            // Title separator
            'separator'   => ' | ',

            // Default description (false = force dynamic per page)
            'description' => false,

            // Keywords are optional (Google ignores them, but still useful)
            'keywords'    => [],

            // Canonical URL (null = current URL)
            'canonical'   => null,

            // Robots directive
            'robots'      => 'index,follow',
        ],

        /*
         * Webmaster verification tags
         */
        'webmaster_tags' => [
            'google'    => env('GOOGLE_SITE_VERIFICATION'),
            'bing'      => env('BING_SITE_VERIFICATION'),
            'yandex'    => env('YANDEX_SITE_VERIFICATION'),
            'pinterest' => null,
            'alexa'     => null,
            'norton'    => null,
        ],

        /*
         * Disable Google translate if needed
         */
        'add_notranslate_class' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | OPENGRAPH (Facebook / WhatsApp / LinkedIn)
    |--------------------------------------------------------------------------
    */
    'opengraph' => [

        'defaults' => [
            'title'       => false,
            'description' => false,
            'url'         => null,
            'type'        => 'website',
            'site_name'   => 'GLS Sprachen Zentrum',
            'images'      => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | TWITTER CARDS
    |--------------------------------------------------------------------------
    */
    'twitter' => [

        'defaults' => [
            'card' => 'summary_large_image',
            'site' => '@glssprachenzentrum', // change if needed
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON-LD (Structured Data Base)
    |--------------------------------------------------------------------------
    */
    'json-ld' => [

        'defaults' => [
            'title'       => false,
            'description' => false,
            'url'         => null,
            'type'        => 'WebSite',
            'images'      => [],
        ],
    ],
];
