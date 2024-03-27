<?php

namespace VVerner\ACF;

defined('ABSPATH') || exit;

class Updates
{
  private string $cacheKey = 'vverner/acf/plugin-update';
  private string $slug = WP_PLUGIN_DIR . '/advanced-custom-fields-pro/acf.php';
  private string $version;

  private function __construct()
  {
  }

  public static function attach(): void
  {
    $cls = new self();

    $cls->load();

    add_filter('site_transient_update_plugins', [$cls, 'update']);
    add_action('upgrader_process_complete', [$cls, 'purge'], 10, 2);
  }

  public function update($transient)
  {
    if (!$transient || empty($transient->checked)) {
      return $transient;
    }

    $remote = $this->request();

    if ($remote && version_compare($this->version, $remote->version, '<')) :
      $update = (object) [
        'slug'        => 'advanced-custom-fields-pro',
        'plugin'      => 'advanced-custom-fields-pro/acf.php',
        'new_version' => $remote->version,
        'package'     => $remote->download_url,
      ];

      $transient->response[$update->plugin] = $update;
    endif;

    return $transient;
  }

  public function purge($upgrader, $options): void
  {
    if ('update' === $options['action'] && 'plugin' === $options['type']) :
      delete_transient($this->cacheKey);
    endif;
  }

  private function load(): void
  {
    if (!function_exists('get_plugin_data')) :
      require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    endif;

    $data = get_plugin_data($this->slug, false, false);

    $this->version = $data['Version'];
  }

  private function request()
  {
    $remote = get_transient($this->cacheKey);

    if (!$remote) :
      $remote = wp_remote_get('https://vverner.com/remote-updates/acf.json?key=' . uniqid());

      if (
        is_wp_error($remote)
        || 200 !== wp_remote_retrieve_response_code($remote)
        || empty(wp_remote_retrieve_body($remote))
      ) {
        return false;
      }

      $remote = json_decode(wp_remote_retrieve_body($remote));

      set_transient($this->cacheKey, $remote, DAY_IN_SECONDS);
    endif;

    return $remote;
  }
}
