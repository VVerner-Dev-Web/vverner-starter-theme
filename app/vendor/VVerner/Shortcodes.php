<?php 

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class Shortcodes
{
   private static $instances = [];

   protected function __construct() 
   { 
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

   public function add(string $sc, array $args = []): void
   {
      add_shortcode(App::PREFIX . $sc, function($atts) use ($sc, $args){
         ob_start();
         $args = shortcode_atts( $args, $atts );
         $args = apply_filters('vv_shortcode-' . $sc, $args);
         $this->getView($sc, $args);

         return ob_get_clean();
      });
   }

   private function getView(string $sc, array $args = []): void
   {
      Views::getInstance()->createForShortcode($sc);
      Views::getInstance()->getForShortcode($sc, $args);
   }
}