<?php defined('ABSPATH') || exit('No direct script access allowed');

$sc = new VVerner\Shortcode('guilherme');


$sc->addAttribute('Sobrenome', 'lastname');
$sc->addAttribute('Idade', 'age', '', [
    19 => '19 anos',
    20 => '20 anos',
    21 => '21 anos'
]);

