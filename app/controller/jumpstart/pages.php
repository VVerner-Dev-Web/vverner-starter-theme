<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('vverner/jumpstart/pages', function () {
    global $wpdb;
    $q = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'vverner_key' AND meta_value = %s";
    $requiredPages = [
        'home'      => 'Início',
        'contact'   => 'Contato',
        'about'     => 'Sobre',
        'blog'      => 'Notícias'
    ];

    foreach ($requiredPages as $key => $page) : 
        $pageQuery  = $wpdb->prepare($q, $key);
        $r          = $wpdb->get_col($pageQuery);

        if ($r) continue;

        wp_insert_post([
            'post_type'     => 'page',
            'post_title'    => $page,
            'post_status'   => 'publish',
            'meta_input'    => [
                'vverner_key'   =>  $key
            ]
        ]);
    endforeach;
});
