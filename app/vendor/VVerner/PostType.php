<?php

namespace VVerner;

use WP_Post;

defined('ABSPATH') || exit('No direct script access allowed');

class PostType
{
    protected $singularName;
    protected $pluralName;
    protected $key;
    protected $icon           = 'dashicons-coffee';
    protected $isPublic       = true;
    protected $allowUxBuilder = false;
    protected $supports       = ['title'];
    protected $metaBoxes      = [];

    public function __construct(string $singular, string $plural, string $key = null)
    {
        $this->singularName  = sanitize_text_field($singular);
        $this->pluralName    = sanitize_text_field($plural);
        $this->key           = $key ? sanitize_title($key) : sanitize_title($singular);
    }

    public static function loadMetaBoxView(WP_Post $post, array $mb)
    {
        $key  = str_replace(App::PREFIX, '',  $mb['id']);
        $view = $post->post_type . '/meta-box/' . $key;
        Views::getInstance()->getView($view, [
            'post' => $post
        ]);
    }

    public function setSupports(array $supports): void
    {
        $this->supports = array_map('sanitize_title', $supports);
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function setPublic(bool $isPublic = true): void
    {
        $this->isPublic = $isPublic;
    }

    public function addUxBuilder(): void
    {
        $this->allowUxBuilder = true;
    }

    public function register(): void
    {
        add_action('init', function () {
            register_post_type($this->key, $this->getRegisterArgs());

            if (function_exists('add_ux_builder_post_type')) :
                add_ux_builder_post_type($this->key);
            endif;
        });

        $this->createDirectories();
        $this->registerMetaBoxes();
        $this->registerHooks();
    }

    private function registerHooks(): void
    {
        add_action('save_post_' . $this->key, function (int $post_ID, WP_Post $post) {
            do_action('vv_' . $this->key . '_updated', $post, $_POST);
        }, 999, 2);

        add_action('delete_post', function (int $post_ID, WP_Post $post) {
            if ($post->post_type === $this->key) :
                do_action('vv_' . $this->key . '_deleted', $post);
            endif;
        }, 99, 2);
    }

    private function registerMetaBoxes(): void
    {
        add_action('add_meta_boxes', function () {
            foreach ($this->metaBoxes as $mb) :
                add_meta_box(
                    $mb['key'],
                    $mb['title'],
                    [__CLASS__, 'loadMetaBoxView'],
                    [$this->key]
                );

                $key = str_replace(App::PREFIX, '',  $mb['key']);
                $file = 'views/' . $this->key . '/meta-box/' . $key . '.php';

                Files::getInstance()->createFile($file);
            endforeach;
        });
    }

    public function addMetaBox(string $title, string $key = null): void
    {
        $key = $key ?? sanitize_title($title);
        $this->metaBoxes[] = [
            'title' => $title,
            'key'   => App::PREFIX . $key
        ];
    }

    private function getRegisterArgs(): array
    {
        $args = [
            'label'                 => $this->singularName,
            'labels'                => $this->getLabels(),
            'supports'              => $this->supports,
            'hierarchical'          => false,
            'show_ui'               => true,
            'menu_position'         => 10,
            'menu_icon'             => $this->icon,
            'capability_type'       => 'page',
            'show_in_admin_bar'     => false,
            'show_in_menu'          => true,
            'can_export'            => false,
            'has_archive'           => $this->isPublic ? sanitize_title($this->pluralName) : false,
            'public'                => $this->isPublic,
            'show_in_nav_menus'     => $this->isPublic,
            'publicly_queryable'    => $this->isPublic,
            'exclude_from_search'   => !$this->isPublic,
            'rewrite'               => $this->getRewriteRules()
        ];

        return apply_filters('vv_post_type_args-' . $this->key, $args);
    }

    private function getLabels(): array
    {
        return [
            'name'                  => $this->pluralName,
            'singular_name'         => $this->singularName,
            'menu_name'             => $this->pluralName,
            'name_admin_bar'        => $this->singularName,
            'archives'              => 'Arquivos de ' . $this->pluralName,
            'attributes'            => 'Atributos para ' . $this->pluralName,
            'parent_item_colon'     => $this->singularName . ' pai:',
            'all_items'             => 'Todos ' . $this->pluralName,
            'add_new_item'          => 'Adicionar ' . $this->singularName,
            'add_new'               => 'Adicionar ' . $this->singularName,
            'new_item'              => 'Adicionar ' . $this->singularName,
            'edit_item'             => 'Editar ' . $this->singularName,
            'update_item'           => 'Atualizar ' . $this->singularName,
            'view_item'             => 'Ver ' . $this->singularName,
            'view_items'            => 'View Items',
            'search_items'          => 'Pesquisar ' . $this->singularName,
            'not_found'             => 'Nada encontrado',
            'not_found_in_trash'    => 'Nada encontrado na lixeira',
            'featured_image'        => 'Imagem em destaque',
            'set_featured_image'    => 'Definir imagem em destaque',
            'remove_featured_image' => 'Remover imagem em destaque',
            'use_featured_image'    => 'Usar como imagem em destaque',
            'insert_into_item'      => 'Inserir em ' . $this->singularName,
            'uploaded_to_this_item' => 'Anexado a este item',
            'items_list'            => 'Lista de ' . $this->pluralName,
            'items_list_navigation' => 'Lista de ' . $this->pluralName,
            'filter_items_list'     => 'Filtrar lista de ' . $this->pluralName,
        ];
    }

    private function getRewriteRules()
    {
        return !$this->isPublic ? false : [
            'slug'                  => sanitize_title($this->singularName),
            'with_front'            => false,
            'pages'                 => true,
            'feeds'                 => false,
        ];
    }

    private function createDirectories(): void
    {
        $files = Files::getInstance();

        $files->createDir('views/' . $this->key);
        $files->createDir('views/' . $this->key . '/meta-box');
    }
}
