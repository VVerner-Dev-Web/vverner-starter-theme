<?php

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class Assets
{
    protected $baseUrl;
    protected $path;
    protected $cssFiles     = [
        'site'      => [],
        'wp-admin'  => []
    ];
    protected $jsFiles      = [
        'site'      => [],
        'wp-admin'  => []
    ];
    protected $jsFilesData  = [
        'site'      => [],
        'wp-admin'  => []
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
        $this->cssFiles['site'][$id] = $file;
    }

    public function registerJs(string $file, string $id = null): void
    {
        $id = $id ? $id : $file;
        $this->jsFiles['site'][$id] = $file;
    }

    public function localizeJs(string $id, array $data): void
    {
        $this->jsFilesData['site'][$id] = $data;
    }

    public function registerAdminCss(string $file, string $id = null): void
    {
        $id = $id ? $id : $file;
        $this->cssFiles['wp-admin'][$id] = $file;
    }

    public function registerAdminJs(string $file, string $id = null): void
    {
        $id = $id ? $id : $file;
        $this->jsFiles['wp-admin'][$id] = $file;
    }

    public function localizeAdminJs(string $id, array $data): void
    {
        $this->jsFilesData['wp-admin'][$id] = $data;
    }

    private function enqueueAssets(): void
    {
        add_action('wp_enqueue_scripts', function () {
            foreach ($this->cssFiles['site'] as $id => $file) :
                wp_enqueue_style(App::PREFIX . $id, $this->getCssFileUrl($file), [], App::VERSION);
            endforeach;

            foreach ($this->jsFiles['site'] as $id => $file) :
                wp_enqueue_script(App::PREFIX . $id, $this->getJsFileUrl($file), ['jquery'], App::VERSION, true);

                if (isset($this->jsFilesData['site'][$id])) :
                    wp_localize_script(App::PREFIX . $id, $id . '_data', $this->jsFilesData['site'][$id]);
                endif;
            endforeach;

            $this->enqueueDynamicAssets();
        }, 999);
    }

    private function enqueueAdminAssets(): void
    {
        add_action('admin_enqueue_scripts', function () {
            foreach ($this->cssFiles['wp-admin'] as $id => $file) :
                wp_enqueue_style(App::PREFIX . $id, $this->getCssFileUrl($file), [], App::VERSION);
            endforeach;

            foreach ($this->jsFiles['wp-admin'] as $id => $file) :
                wp_enqueue_script(App::PREFIX . $id, $this->getJsFileUrl($file), ['jquery'], App::VERSION, true);

                if (isset($this->jsFilesData['wp-admin'][$id])) :
                    $var = str_replace('-', '_', sanitize_title($id)) . '_data';
                    wp_localize_script(App::PREFIX . $id, $var, $this->jsFilesData['wp-admin'][$id]);
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
                    App::VERSION
                );
            endif;

            if ($files->exists('assets/css/' . $typeFile . '.css')) :
                wp_enqueue_style(
                    App::PREFIX . $post->post_type,
                    $this->getCssFileUrl($typeFile),
                    [],
                    App::VERSION
                );
            endif;

            if ($files->exists('assets/js/' . $postFile . '.js')) :
                wp_enqueue_script(
                    App::PREFIX . $post->post_type . '-' . $post->post_name,
                    $this->getJsFileUrl($postFile),
                    ['jquery'],
                    App::VERSION,
                    true
                );
            endif;

            if ($files->exists('assets/js/' . $typeFile . '.js')) :
                wp_enqueue_script(
                    App::PREFIX . $post->post_type,
                    $this->getJsFileUrl($typeFile),
                    ['jquery'],
                    App::VERSION,
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
