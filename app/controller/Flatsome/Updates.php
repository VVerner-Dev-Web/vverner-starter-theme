<?php

namespace VVerner\Flatsome;

defined('ABSPATH') || exit;

class Updates
{
  private string $cacheKey = 'vverner/flatsome/theme-update';
  private string $slug = WP_PLUGIN_DIR . '/advanced-custom-fields-pro/acf.php';
  private string $version;

  private function __construct()
  {
  }

  public static function attach(): void
  {
    $cls = new self();

    add_filter('site_transient_update_themes', [$cls, 'update']);
  }
  public function update($transient)
  {
    $stylesheet = get_template();
    $flatsome   = wp_get_theme()->parent();

    $version    = $flatsome->get('Version');
    $remote     = $this->request();

    if (!$remote) :
      return $transient;
    endif;

    $data = [
      'theme'       => $flatsome->get_stylesheet(),
      'url'         => $remote->details_url,
      'new_version' => $remote->version,
      'package'     => $remote->download_url,
    ];

    $remote && version_compare($version, $remote->version, '<') ?
      $transient->response[$stylesheet] = $data :
      $transient->no_update[$stylesheet] = $data;

    return $transient;
  }

  private function request()
  {
    $remote = get_transient($this->cacheKey);

    if (!$remote) :
      $remote = wp_remote_get('https://vverner.com/remote-updates/flatsome.json?key=' . uniqid());

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
