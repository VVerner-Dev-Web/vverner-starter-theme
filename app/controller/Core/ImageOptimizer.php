<?php

namespace VVerner\Core;

use WP_Error;

defined('ABSPATH') || exit('No direct script access allowed');

class ImageOptimizer
{
  private function __construct()
  {
  }

  public static function attach(): void
  {
    $cls = new self();
    add_filter('wp_handle_upload', $cls->resize(...));
  }

  public function resize(WP_Error|array $upload): WP_Error|array
  {
    $maxSize = VV_MAX_IMAGE_SIZE_IN_PIXELS;
    $quality = VV_IMAGE_QUALITY;

    $types = [
      'image/jpeg',
      'image/jpg',
      'image/webp',
      'image/png'
    ];

    if (
      is_wp_error($upload) ||
      !in_array($upload['type'], $types)
      || filesize($upload['file']) <= 0
    ) :
      return $upload;
    endif;

    $editor = wp_get_image_editor($upload['file']);
    $imageSize = $editor->get_size();
    if (isset($imageSize['width']) && $imageSize['width'] > $maxSize) :
      $editor->resize($maxSize, null, false);
    endif;
    $imageSize = $editor->get_size();
    if (isset($imageSize['height']) && $imageSize['height'] > $maxSize) :
      $editor->resize(null, $maxSize, false);
    endif;
    $editor->set_quality($quality);

    $editor->save($upload['file']);

    return $upload;
  }
}
