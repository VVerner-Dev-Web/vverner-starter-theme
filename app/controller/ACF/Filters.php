<?php

namespace VVerner\ACF;

defined('ABSPATH') || exit('No direct script access allowed');

class Filters
{
  private function __construct()
  {
  }

  public static function attach(): void
  {
    $cls = new self();
    add_filter('acf/settings/save_json', $cls->saveJson(...));
    add_filter('acf/settings/load_json', $cls->loadPath(...));
  }

  public function saveJson(): string
  {
    return __DIR__ . '/json';
  }

  public function loadPath(array $paths): array
  {
    $paths[] = __DIR__ . DIRECTORY_SEPARATOR . 'json';
    return $paths;
  }
}
