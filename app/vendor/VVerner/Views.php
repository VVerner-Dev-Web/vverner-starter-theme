<?php 

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class Views
{
   protected $path;

   private static $instances = [];

   protected function __construct() 
   { 
      $this->path = VV_APP . '/views/';
   }

   protected function __clone() 
   {
   }

   public function __wakeup()
   {
      throw new Exception("Cannot unserialize this class.");
   }

   public static function getInstance(): self
   {
      $cls = static::class;
      if (!isset(self::$instances[$cls])) :
         self::$instances[$cls] = new static();
      endif;

      return self::$instances[$cls];
   }

   public function getForShortcode(string $sc, array $context = []): void
   {
      $this->getView('shortcodes/' . $sc, $context);
   }

   public function getComponent(string $component, array $context = []): void
   {
      $this->getView('components/' . $component, $context);
   }

   public function getView(string $view, array $context = []): void
   {
      $file = $this->path . $view . '.php';

      if (file_exists($file)) : 
         extract($context);
         require $file;
      else : 
         throw new Exception('View not found: ' . $file);
      endif;
   }

   public function createForShortcode(string $sc): void
   {
      $file = 'views/shortcodes/' . $sc . '.php';
      Files::getInstance()->createFile($file);
   }
}