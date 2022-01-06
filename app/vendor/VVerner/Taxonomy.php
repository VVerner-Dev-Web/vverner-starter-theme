<?php 

namespace VVerner;

defined('ABSPATH') || exit('No direct script access allowed');

class Taxonomy
{
   protected $singularName;
   protected $pluralName;
   protected $key;
   protected $cpt          = 'post';
   protected $isPublic     = true;

   public function __construct(string $singular, string $plural, string $key = null)
   {
      $this->singularName  = sanitize_text_field($singular);
      $this->pluralName    = sanitize_text_field($plural);
      $this->key           = $key ? sanitize_title($key) : sanitize_title($singular);
   }

   public function setPublic(bool $isPublic = true): void
   {
      $this->isPublic = $isPublic;
   }

   public function setPostType(string $postType): void
   {
      $this->cpt = $postType;
   }

   public function register(): void
   {
      add_action('init', function(){
	      register_taxonomy($this->cpt . '-' . $this->key , [$this->cpt], $this->getRegisterArgs() );
      });
   }

   private function getRegisterArgs(): array
   {
      $args = [
         'labels'                => $this->getLabels(),
         'hierarchical'          => false,
         'public'                => $this->isPublic,
         'show_ui'               => true,
         'show_admin_column'     => true,
         'show_in_nav_menus'     => $this->isPublic,
         'show_tagcloud'         => false,
      ];

      return apply_filters('vv_taxonomy_args-' . $this->key, $args);
   }

   private function getLabels(): array
   {
      return [
         'name'                  => $this->pluralName,
         'singular_name'         => $this->singularName,
         'menu_name'             => $this->pluralName,
         'parent_item_colon'     => $this->singularName . ' pai:',
         'all_items'             => 'Todos ' . $this->pluralName,
         'add_new_item'          => 'Adicionar ' . $this->singularName,
         'edit_item'             => 'Editar ' . $this->singularName,
         'update_item'           => 'Atualizar ' . $this->singularName,
         'view_item'             => 'Ver ' . $this->singularName,
         'search_items'          => 'Pesquisar ' . $this->singularName,
         'not_found'             => 'Nada encontrado',
         'items_list'            => 'Lista de ' . $this->pluralName,
         'items_list_navigation' => 'Lista de ' . $this->pluralName,
         'add_or_remove_items'   => 'Adicionar ou remover ' . $this->pluralName,
         'choose_from_most_used' => 'Escolher dentre os mais usados',
         'popular_items'         => 'Mais usados',
         'search_items'          => 'Pesquisar',
         'separate_items_with_commas' => 'Separe os valores por vírgula',
      ];
   }
}