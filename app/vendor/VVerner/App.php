<?php 

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class App 
{
   private static $instances = [];

   public const VERSION = '2.0.0';
   public const PREFIX  = 'vv-';

   protected function __construct() 
   { 
      $this->assets     = Assets::getInstance();
      $this->views      = Views::getInstance();
      $this->shortcodes = Shortcodes::getInstance();

      add_action('init', function(){
         global $vverner_app;
         $vverner_app = $this;
      });
   }

   protected function __clone() 
   {
   }

   public function __wakeup()
   {
      throw new Exception("Cannot unserialize a singleton.");
   }

   public static function getInstance(): self
   {
      $cls = static::class;
      if (!isset(self::$instances[$cls])) :
         self::$instances[$cls] = new static();
      endif;

      return self::$instances[$cls];
   }

   public function isVVernerUser(): bool
   {
      $data = get_userdata(get_current_user_id());
      return strpos($data->user_email, 'vverner') !== false;
   }

   public function log($thing, bool $print = false): void
   {
      error_log('=== VV_LOG ===');
      error_log(print_r($thing, true));

      if ($print && $this->isVVernerUser()) : 
         add_action('the_content', function() use ($thing){
            echo '<pre>';
            var_dump($thing);
            echo '<pre>';
         });
      endif;
   }
}