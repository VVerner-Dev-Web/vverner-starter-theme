<?php defined('ABSPATH') || exit('No direct script access allowed');

add_filter('acf/settings/save_json', function($path){
   return __DIR__ . DIRECTORY_SEPARATOR . 'json';
});

add_filter('acf/settings/load_json', function($paths){
   $paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'json';
   return $paths;
});
