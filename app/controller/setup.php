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
 * Altera o REST-API header de "null" para "*".
 */
add_action( 'rest_api_init', function(){
	header( 'Access-Control-Allow-Origin: *' );
});

/**
 * Remove os emojis padrões do wp
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
