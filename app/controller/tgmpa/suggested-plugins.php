<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('tgmpa_register', function(){
    $isDev = VVerner\App::isDevMode();
    $alwaysRequired        = true;
    $requiredInProduction  = !$isDev;

    $plugins = [
        [
            'name'      => 'Contact Form 7',
            'slug'      => 'contact-form-7',
            'required'  => $alwaysRequired,
        ],
        [
            'name'      => 'Contact Form 7 Database Addon – CFDB7',
            'slug'      => 'contact-form-cfdb7',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'Smush – Compress, Optimize and Lazy Load Images',
            'slug'      => 'wp-smushit',
            'required'  => $alwaysRequired,
        ],
        [
            'name'      => 'Rank Math – SEO Plugin for WordPress',
            'slug'      => 'seo-by-rank-math',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'Really Simple SSL',
            'slug'      => 'really-simple-ssl',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'iThemes Security (formerly Better WP Security)',
            'slug'      => 'better-wp-security',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'Autoptimize',
            'slug'      => 'autoptimize',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'Autoclear Autoptimize Cache',
            'slug'      => 'autoclear-autoptimize-cache',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'Post SMTP Mailer/Email Log',
            'slug'      => 'post-smtp',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'Site Kit by Google',
            'slug'      => 'google-site-kit',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'Akismet Spam Protection',
            'slug'      => 'akismet',
            'required'  =>  $requiredInProduction,
        ],
        [
            'name'      => 'GDPR Cookie Consent',
            'slug'      => 'cookie-law-info',
            'required'  => $alwaysRequired,
        ]
    ];

    tgmpa($plugins, [
        'id'           => 'vverner',
        'default_path' => '',
        'menu'         => 'tgmpa-install-plugins',
        'parent_slug'  => 'themes.php',
        'capability'   => 'edit_theme_options',
        'has_notices'  => true,
        'dismissable'  => true,
        'dismiss_msg'  => '',
        'is_automatic' => true,
        'message'      => 'Ative os plugins recomendados para tirar o melhor proveito de seu site em WordPress.',
    ]);
});
