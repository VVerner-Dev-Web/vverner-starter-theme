<?php

namespace VVerner\Core;

defined('ABSPATH') || exit('No direct script access allowed');

class AutoLoader
{
  private function __construct()
  {
  }

  public static function attach(): void
  {
    $cls = new self;
    $cls->load();
  }

  private function load(string $path = null): void
  {
    if ($path === null || $path === '' || $path === '0') :
      $path = VV_APP . '/controller';
    endif;

    $path = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $path);

    if (is_file($path)) :
      $this->loadFile($path);
      return;
    endif;

    $ignoredFiles = ['index.php', '..', '.'];
    $dependencies = array_diff(scandir($path), $ignoredFiles);

    $files = array_filter($dependencies, fn ($dependency): bool => is_file($path . DIRECTORY_SEPARATOR . $dependency));
    $dependencies = array_diff($dependencies, $files);

    foreach ($files as $file) :
      $this->loadFile($path . DIRECTORY_SEPARATOR . $file);
    endforeach;

    foreach ($dependencies as $dependency) :
      $this->load($path . DIRECTORY_SEPARATOR . $dependency);
    endforeach;
  }

  private function loadFile(string $path): void
  {
    if (str_ends_with($path, '.php')) :
      require_once $path;
      $this->attachClass($path);
    endif;
  }

  private function attachClass(string $path): void
  {
    if (str_ends_with($path, 'AutoLoader.php')) :
      return;
    endif;

    $className = explode(DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR, $path);
    $className = '\\VVerner\\' . str_replace('.php', '', end($className));
    $className = str_replace('/', '\\', $className);

    if (!class_exists($className) || !method_exists($className, 'attach') || str_contains($className, '\Adapter\\')) {
      return;
    }

    call_user_func([$className, 'attach']);
  }
}

AutoLoader::attach();
