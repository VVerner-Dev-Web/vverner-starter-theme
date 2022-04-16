<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('vverner/jumpstart/configs', function () {
    // Configurações
    update_option('blogdescription', '');
    update_option('timezone_string', 'America/Sao_Paulo');
    update_option('date_format', 'd/m/Y');
    update_option('time_format', 'H:i');

    // Leitura
    global $wpdb;
    $home = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'vverner_key' AND meta_value = 'home'";
    $blog = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'vverner_key' AND meta_value = 'blog'";

    update_option('show_on_front', 'page');
    update_option('page_on_front', $wpdb->get_var($home));
    update_option('page_for_posts', $wpdb->get_var($blog));
    update_option('posts_per_page', 12);
});
