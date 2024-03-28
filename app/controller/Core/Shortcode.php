<?php

namespace VVerner\Core;

defined('ABSPATH') || exit('No direct script access allowed');

class Shortcode
{
  private ?string $uxBuilderName = null;
  private array $atts = [];
  private array $options = [];

  public function __construct(private readonly string $name)
  {
    $this->addDefaultAttributes();

    add_action('init', $this->addShortcode(...));
    add_action('ux_builder_setup', $this->uxBuilderSetup(...));
  }

  public function setUxBuilderName(string $uxBuilderName): void
  {
    $this->uxBuilderName = $uxBuilderName;
  }

  public function addShortcode(): void
  {
    add_shortcode('vverner_' . $this->name, function ($args): string|false {
      ob_start();

      $args = shortcode_atts($this->atts, $args);
      $file = VV_APP . '/views/' . $this->name . '.php';

      if (!file_exists($file)) :
        file_put_contents($file, 'auto generate');
      endif;

      require_once $file;

      return ob_get_clean();
    });
  }

  public function uxBuilderSetup(): void
  {
    if (!function_exists('add_ux_builder_shortcode')) :
      require_once get_template_directory() . '/inc/builder/helpers.php';
    endif;

    add_ux_builder_shortcode('vverner_' . $this->name, [
      'name'              => $this->uxBuilderName ?: $this->name,
      'category'          => 'VVerner',
      'options'           => $this->options
    ]);
  }

  public function addAttribute(string $heading, string $key, $defaultValue = '', array $options = []): void
  {
    $this->atts[$key] = $defaultValue;
    $this->options[$key] = [
      'type'       => $options !== [] ? 'select' : 'textfield',
      'heading'    => $heading,
      'default'    => $defaultValue,
      'options'    => $options,
      'full_width' => true,
    ];
  }

  private function addDefaultAttributes(): void
  {
    $this->addAttribute('Classe extra de CSS', 'class', '');
    $this->addAttribute('ID', 'id', '');
  }
}
