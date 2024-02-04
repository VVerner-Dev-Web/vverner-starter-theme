<?php

use VVerner\Logger;

 defined('ABSPATH') || exit('No direct script access allowed');

require_once __DIR__ . '/constants.php';

require_once VV_APP . '/controller/VVerner/App.php';

VVerner\App::loadDependencies(VV_APP . '/controller/VVerner');
VVerner\App::loadDependencies(VV_APP . '/controller');

VVerner\App::attachAjaxMiddleware();
VVerner\App::attachJumpStart();

$logger = Logger::getInstace();
define('VV_LOGGER', $logger);