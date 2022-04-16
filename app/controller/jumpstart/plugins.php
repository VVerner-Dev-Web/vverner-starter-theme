<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('vverner/jumpstart/plugins', function () {
    $plugins = [
        'contact-form-7',
        'contact-form-cfdb7',
        'wp-smushit',
        'akismet',
        'cookie-law-info'
    ];

    foreach ($plugins as $plugin) :
        $request = new WP_REST_Request('POST', '/wp/v2/plugins');
        $request->set_param('slug', $plugin);
        $request->set_param('status', 'active');

        rest_do_request($request);
    endforeach;
});
