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
 * Limpa o conteúdo dos posts de erros comuns
 */
add_filter('the_content', function ($content) {
    $patterns = [
        '&nbsp;',
        '<p></p>',
        '<p>&nbsp;</p>',
        '<p>&nbsp;</p>',
        '<p class="wp-block-paragraph"></p>',
        '<li></li>'
    ];

    foreach ($patterns as $pattern) :
        $content = str_replace($pattern, '', $content);
    endforeach;

    return $content;
  
}, 100);

/**
 * Trigger para iniciar o jumpstart
 */
add_action('init', function(){
    if (defined('VV_DO_JUMPSTART') && VV_DO_JUMPSTART) :
        do_action('vverner/jumpstart');
    endif;
});
