<?php defined('ABSPATH') || exit('No direct script access allowed');

$assets = VVerner\Assets::getInstance();
$assets->registerCss('main');
$assets->registerJs('app');
