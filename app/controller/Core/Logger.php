<?php

namespace VVerner\Core;

class Logger
{
  private static array $logger = [];

  public static function instance(): Logger
  {
    $cls = static::class;
    if (!isset(self::$logger[$cls])) :
      self::$logger[$cls] = new static();
    endif;

    return self::$logger[$cls];
  }

  public function dump(mixed $thing): void
  {
    if (isVVernerUser()) :
      add_action('init', function () use ($thing): never {
        echo '<pre>';
        var_dump($thing);
        echo '<pre>';
        exit;
      });
    endif;
  }

  public function write(mixed $message, string $filename = null): void
  {
    $filename = $this->createLogFile($filename);

    $handler = fopen($filename, 'a');
    fwrite($handler, '[' . current_time('Y-m-d H:i:s') . '] - ' . print_r($message, true) . " \n");
    fclose($handler);
  }

  private function createLogFile(?string $filename = null): string
  {
    $filename ??= current_time('Y-m-d') . '.log';

    if (!str_ends_with($filename, '.log')) :
      $filename .= '.log';
    endif;

    $filename = VV_APP . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $filename;

    if (!file_exists($filename)) :
      file_put_contents($filename, '');
    endif;

    $handler = fopen($filename, 'a');
    fwrite($handler, '[' . current_time('Y-m-d H:i:s') . "] - Log Created" . PHP_EOL);
    fclose($handler);

    return $filename;
  }
}
