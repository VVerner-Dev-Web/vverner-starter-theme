<?php defined('ABSPATH') || exit('No direct script access allowed');

function vvernerThemeAutoLoader(string $path = null): void
{
  if (!$path) :
    $path = VV_APP . '/controller';
  endif;

  $path = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $path);

  if (is_dir($path)) :
    $ignoredFiles = ['index.php', '..', '.'];
    $dependencies = array_diff(scandir($path), $ignoredFiles);

    foreach ($dependencies as $dependency) :
      vvernerThemeAutoLoader($path . DIRECTORY_SEPARATOR . $dependency);
    endforeach;

  elseif (is_file($path)) :
    if (substr($path, -4) === '.php') :
      require_once $path;

      $className = explode(DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR, $path);
      $className = '\\VVerner\\' . str_replace('.php', '', end($className));
      $className = str_replace('/', '\\', $className);

      if (class_exists($className) && method_exists($className, 'attach')) :
        error_log($className . ' attached');
        call_user_func([$className, 'attach']);
      endif;
    endif;

  endif;
}

vvernerThemeAutoLoader();
