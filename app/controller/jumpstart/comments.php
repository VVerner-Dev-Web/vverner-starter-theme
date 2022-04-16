<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('vverner/jumpstart/comments', function () {
    global $wpdb;
    $wpdb->delete(
        $wpdb->comments,
        ['comment_post_ID' => '1']
    );
});
