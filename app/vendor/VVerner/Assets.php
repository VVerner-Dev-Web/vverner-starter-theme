<?php 

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class Assets 
{
   protected $baseUrl;
   protected $path;
   protected $cssFiles     = [];
   protected $jsFiles      = [];
   protected $jsFilesData  = [];

   private static $instances = [];

   protected function __construct() 
   { 
      $this->path    = VV_APP . '/assets/';
      $this->baseUrl = VV_APP_URL . '/assets/';
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

   public function getImageFileUrl(string $filename): string
   {
      return $this->getUrl('img', $filename);
   }

   public function getCssFileUrl(string $filename): string
   {
      return $this->getUrl('css', $filename);
   }

   public function getJsFileUrl(string $filename): string
   {
      return $this->getUrl('js', $filename);
   }

   public function registerCss(string $file, string $id = null): void
   {
      $id = $id ? $id : $file;
      $this->cssFiles[$id] = $file;
   }

   public function registerJs(string $file, string $id = null): void
   {
      $id = $id ? $id : $file;
      $this->jsFiles[$id] = $file;
   }

   public function localizeJs(string $id, array $data): void
   {
      $this->jsFilesData[$id] = $data; 
   }

   public function enqueueAssets(): void
   {
      add_action('wp_enqueue_scripts', function() {
         foreach ($this->cssFiles as $id => $file) : 
            wp_enqueue_style(App::PREFIX . $id, $this->getCssFileUrl($file), [], App::VERSION);
         endforeach;

         foreach ($this->jsFiles as $id => $file) : 
            wp_enqueue_script(App::PREFIX . $id, $this->getJsFileUrl($file), ['jquery'], App::VERSION, true);

            if (isset($this->jsFilesData[$id])) : 
               wp_localize_script(App::PREFIX . $id, $id . '_data', $this->jsFilesData[ $id ]);
            endif;
         endforeach;

         $this->enqueuePostAssets();
      });
   }

   private function enqueuePostAssets(): void
   {
      global $post;

      if ($post) : 
         $postKey  = $post->post_type . '/' . $post->post_name;
         $cssFile  = 'assets/css/' . $postKey . '.css';
         $jsFile   = 'assets/js/' . $postKey . '.js';

         do_action('vv_assets-' . $postKey);


         $files = Files::getInstance();

         if ($files->exists($cssFile)) : 
            wp_enqueue_style (
               App::PREFIX . $post->post_type . '-' . $post->post_name,
               $this->getCssFileUrl($postKey),
               [],
               App::VERSION
            );
         endif;

         if ($files->exists($jsFile)) : 
            wp_enqueue_script(
               App::PREFIX . $post->post_type . '-' . $post->post_name,
               $this->getJsFileUrl($postKey),
               ['jquery'],
               App::VERSION,
               true
            );
         endif;
      endif;
   }

   private function getUrl(string $type, string $filename): string
   {
      return $this->baseUrl . '/'. $type .'/' . $filename . '.' . $type; 
   }
}