<?php defined('ABSPATH') || exit('No direct script access allowed');

add_action('vverner/jumpstart', function () {
  update_option('vverner_theme-jumpstart', 1, false);

  do_action('vverner/jumpstart/posts');
  do_action('vverner/jumpstart/pages');
  do_action('vverner/jumpstart/comments');
  do_action('vverner/jumpstart/plugins');
  do_action('vverner/jumpstart/configs');
});
