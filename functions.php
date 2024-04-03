<?php

defined('ABSPATH') || exit('No direct script access allowed');

require_once __DIR__ . '/constants.php';

if (!class_exists('VVerner\Core\AutoLoader')) :
  add_action('admin_notices', function () {
    echo '<div class="error"><p><strong>Erro:</strong> O site atual depende do plugin VVerner - Toolbox para funcionar. Por favor, contate a equipe VVerner para mais informações.</p></div>';
  });
else :
  (new VVerner\Core\AutoLoader('ThemeName', get_stylesheet_directory() . '/src'))->load();
endif;


/**
 * Insira seus códigos abaixo
 */
