<?php defined('ABSPATH') || exit('No direct script access allowed');

require_once VV_APP . '/vendor/VVerner/App.php';

VVerner\App::loadDependencies( VV_APP . '/vendor/VVerner' );
VVerner\App::loadDependencies( VV_APP . '/vendor/TGM' );
VVerner\App::loadDependencies( VV_APP . '/controller' );