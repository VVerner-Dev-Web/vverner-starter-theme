<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('tgmpa_register', function(){
   $plugins = [
      [
         'name'      => 'Contact Form 7',
         'slug'      => 'contact-form-7',
         'required'  => true,
      ],
      [
         'name'      => 'Contact Form 7 Database Addon – CFDB7',
         'slug'      => 'contact-form-cfdb7',
         'required'  => false,
      ],
      [
         'name'      => 'Smush – Compress, Optimize and Lazy Load Images',
         'slug'      => 'wp-smushit',
         'required'  => true,
      ],
      [
         'name'      => 'Rank Math – SEO Plugin for WordPress',
         'slug'      => 'seo-by-rank-math',
         'required'  => false,
      ],
      [
         'name'      => 'Really Simple SSL',
         'slug'      => 'really-simple-ssl',
         'required'  => false,
      ],
      [
         'name'      => 'iThemes Security (formerly Better WP Security)',
         'slug'      => 'better-wp-security',
         'required'  => false,
      ],
      [
         'name'      => 'Autoptimize',
         'slug'      => 'autoptimize',
         'required'  => false,
      ],
      [
         'name'      => 'Autoclear Autoptimize Cache',
         'slug'      => 'autoclear-autoptimize-cache',
         'required'  => false,
      ],
      [
         'name'      => 'Post SMTP Mailer/Email Log',
         'slug'      => 'post-smtp',
         'required'  => false,
      ],
      [
         'name'      => 'Site Kit by Google',
         'slug'      => 'google-site-kit',
         'required'  => false,
      ],
      [
         'name'      => 'Akismet Spam Protection',
         'slug'      => 'akismet',
         'required'  => false,
      ],
      [
         'name'      => 'GDPR Cookie Consent',
         'slug'      => 'cookie-law-info',
         'required'  => true,
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