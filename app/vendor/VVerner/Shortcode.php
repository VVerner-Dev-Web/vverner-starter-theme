<?php

namespace VVerner;

defined('ABSPATH') || exit('No direct script access allowed');

class Shortcode
{
    private $name;
    private $atts = [];
    private $options = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->addDefaultAttributes();
        add_action('init', [$this, 'addShortcode']);
        add_action('ux_builder_setup', [$this, 'uxBuilderSetup']);
    }

    public function addShortcode(): void
    {
        add_shortcode(App::PREFIX . $this->name, function ($args) {
            ob_start();

            $args = shortcode_atts($this->atts, $args);
            $args = apply_filters('vv_shortcode-' . $this->name, $args);
            $this->getView($this->name, $args);

            return ob_get_clean();
        });
    }

    public function uxBuilderSetup(): void
    {
        if (!function_exists('add_ux_builder_shortcode')) :
            require_once get_template_directory() . '/inc/builder/helpers.php';
        endif;

        add_ux_builder_shortcode(App::PREFIX . $this->name, [
            'name'              => $this->name,
            'category'          => 'VVerner',
            'options'           => $this->options,
        ]);
    }

    public function addAttribute(string $heading, string $key, $defaultValue = '', array $options = []): void
    {
        $this->atts[ $key ] = $defaultValue;
        $this->options[ $key ] = [
            'type'       => $options ? 'select' : 'textfield',
            'heading'    => $heading,
            'default'    => $defaultValue,
            'options'    => $options,
            'full_width' => true,
        ];
    }

    private function addDefaultAttributes(): void
    {
        $this->addAttribute('Classe extra de CSS', 'class', '');
    }

    private function getView(string $sc, array $args = []): void
    {
        Views::getInstance()->createForShortcode($sc);
        Views::getInstance()->getForShortcode($sc, $args);
    }
}
