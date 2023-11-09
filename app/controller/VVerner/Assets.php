<?php

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class Assets
{
  protected $baseUrl;
  protected $path;
  protected $localCssFiles     = [
    'site'      => [],
    'wp-admin'  => [],
  ];
  protected $localJsFiles      = [
    'site'      => [],
    'wp-admin'  => [],
  ];
  protected $localJsFilesData  = [
    'site'      => [],
    'wp-admin'  => []
  ];
  protected $externalCssFiles  = [
    'site'      => [],
    'wp-admin'  => [],
  ];
  protected $externalJsFiles   = [
    'site'      => [],
    'wp-admin'  => [],
  ];

  private static $instances = [];

  protected function __construct()
  {
    $this->path    = VV_APP . '/assets/';
    $this->baseUrl = VV_APP_URL . '/assets/';

    $this->enqueueAssets();
    $this->enqueueAdminAssets();
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
    return $this->baseUrl . 'img/' . $filename;
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
    $this->localCssFiles['site'][$id] = $file;
  }

  public function registerJs(string $file, string $id = null): void
  {
    $id = $id ? $id : $file;
    $this->localJsFiles['site'][$id] = $file;
  }

  public function registerExternalCss(string $url, string $id, string $loadOn = 'site'): void
  {
    $this->externalCssFiles[$loadOn][$id] = $url;
  }

  public function registerExternalJs(string $url, string $id, string $loadOn = 'site'): void
  {
    $this->externalJsFiles[$loadOn][$id] = $url;
  }

  public function localizeJs(string $id, array $data): void
  {
    $this->localJsFilesData['site'][$id] = $data;
  }

  public function registerAdminCss(string $file, string $id = null): void
  {
    $id = $id ? $id : $file;
    $this->localCssFiles['wp-admin'][$id] = $file;
  }

  public function registerAdminJs(string $file, string $id = null): void
  {
    $id = $id ? $id : $file;
    $this->localJsFiles['wp-admin'][$id] = $file;
  }

  public function localizeAdminJs(string $id, array $data): void
  {
    $this->localJsFilesData['wp-admin'][$id] = $data;
  }

  private function enqueueAssets(): void
  {
    add_action('wp_enqueue_scripts', function () {


      foreach ($this->externalCssFiles['site'] as $id => $url) :
        wp_enqueue_style(App::PREFIX . $id, $url, [], null);
      endforeach;

      foreach ($this->localCssFiles['site'] as $id => $file) :
        wp_enqueue_style(App::PREFIX . $id, $this->getCssFileUrl($file), [], App::getVersion());
      endforeach;

      foreach ($this->externalJsFiles['site'] as $id => $url) :
        wp_enqueue_script(App::PREFIX . $id, $url, ['jquery'], null, true);
      endforeach;

      foreach ($this->localJsFiles['site'] as $id => $file) :
        wp_enqueue_script(App::PREFIX . $id, $this->getJsFileUrl($file), ['jquery'], App::getVersion(), true);

        if (isset($this->localJsFilesData['site'][$id])) :
          wp_localize_script(App::PREFIX . $id, $id . '_data', $this->localJsFilesData['site'][$id]);
        endif;
      endforeach;

      $this->enqueueDynamicAssets();
    }, 999);
  }

  private function enqueueAdminAssets(): void
  {
    add_action('admin_enqueue_scripts', function () {
      foreach ($this->externalCssFiles['wp-admin'] as $id => $url) :
        wp_enqueue_style(App::PREFIX . $id, $url, [], null);
      endforeach;

      foreach ($this->localCssFiles['wp-admin'] as $id => $file) :
        wp_enqueue_style(App::PREFIX . $id, $this->getCssFileUrl($file), [], App::getVersion());
      endforeach;

      foreach ($this->externalJsFiles['wp-admin'] as $id => $url) :
        wp_enqueue_script(App::PREFIX . $id, $url, ['jquery'], null, true);
      endforeach;

      foreach ($this->localJsFiles['wp-admin'] as $id => $file) :
        wp_enqueue_script(App::PREFIX . $id, $this->getJsFileUrl($file), ['jquery'], App::getVersion(), true);

        if (isset($this->localJsFilesData['wp-admin'][$id])) :
          $var = str_replace('-', '_', sanitize_title($id)) . '_data';
          wp_localize_script(App::PREFIX . $id, $var, $this->localJsFilesData['wp-admin'][$id]);
        endif;
      endforeach;
    }, 999);
  }

  private function enqueueDynamicAssets(): void
  {
    global $post;

    if ($post) :
      $typeFile  = $post->post_type;
      $postFile  = $post->post_type . '/' . $post->post_name;

      do_action('vv_assets-' . $typeFile);

      $files = Files::getInstance();

      if ($files->exists('assets/css/' . $postFile . '.css')) :
        wp_enqueue_style(
          App::PREFIX . $post->post_type . '-' . $post->post_name,
          $this->getCssFileUrl($postFile),
          [],
          App::getVersion()
        );
      endif;

      if ($files->exists('assets/css/' . $typeFile . '.css')) :
        wp_enqueue_style(
          App::PREFIX . $post->post_type,
          $this->getCssFileUrl($typeFile),
          [],
          App::getVersion()
        );
      endif;

      if ($files->exists('assets/js/' . $postFile . '.js')) :
        wp_enqueue_script(
          App::PREFIX . $post->post_type . '-' . $post->post_name,
          $this->getJsFileUrl($postFile),
          ['jquery'],
          App::getVersion(),
          true
        );
      endif;

      if ($files->exists('assets/js/' . $typeFile . '.js')) :
        wp_enqueue_script(
          App::PREFIX . $post->post_type,
          $this->getJsFileUrl($typeFile),
          ['jquery'],
          App::getVersion(),
          true
        );
      endif;
    endif;
  }

  private function getUrl(string $type, string $filename): string
  {
    return $this->baseUrl . $type . '/' . $filename . '.' . $type;
  }
}
