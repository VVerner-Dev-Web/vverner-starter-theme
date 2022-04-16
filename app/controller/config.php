<?php defined('ABSPATH') || exit('No direct script access allowed');

// Descomentar hook abaixo para executar o jumpstart em novas instalações
// add_action('init', function(){
//     do_action('vverner/jumpstart');
// });

$assets = VVerner\Assets::getInstance();
$assets->registerCss('main');

$assets->registerJs('app'); 
$assets->localizeJs('app', [
    'sec'    => VVerner\AjaxAPI::getInstance()->getGlobalNonce(),
    'action' => VVerner\AjaxAPI::getInstance()->getGlobalAction(),
    'url'    => VVerner\AjaxAPI::getInstance()->getRequestUrl()
]); 
