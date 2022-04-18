<?php defined('ABSPATH') || exit('No direct script access allowed');

/**
 * Remove as meta tags do WP do head
 */
add_filter( 'the_generator', '__return_false' );

/**
 * Desabilita o XMLRPC.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove os emojis padrões do wp
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

/**
 * Trigger para iniciar o jumpstart. Descomentar hook abaixo para executar.
 */
// add_action('init', function(){
//     do_action('vverner/jumpstart');
// });


/**
 * Jumpstart
 */
add_action('vverner/jumpstart', function(){
    if (get_option('vverner_theme-jumpstart')) : 
        return;
    endif;
    
    update_option('vverner_theme-jumpstart', 1, false);

    do_action('vverner/jumpstart/posts');
    do_action('vverner/jumpstart/pages');
    do_action('vverner/jumpstart/comments');
    do_action('vverner/jumpstart/plugins');
    do_action('vverner/jumpstart/configs');
});
