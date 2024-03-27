<?php

namespace VVerner\Core;

use WP_REST_Request;

defined('ABSPATH') || exit('No direct script access allowed');

class JumpStart
{
  private function __construct()
  {
  }

  public static function attach(): void
  {
    $cls = new self();

    add_action('after_switch_theme', [$cls, 'loadScript']);
    add_action('init', [$cls, 'maybeRun']);
  }

  public function loadScript(): void
  {
    if (get_option('vverner_theme-jumpstart')) :
      return;
    endif;
?>
    <script>
      const confirmed = confirm('Rodar o jumpstart?');
      location.href = '<?= admin_url() ?>?jumpstart=' + confirmed;
    </script>
<?php
  }

  public function maybeRun(): void
  {
    if (!filter_input(INPUT_GET, 'jumpstart') == 'true') :
      return;
    endif;

    $this->lock();
    $this->posts();
    $this->pages();
    $this->comments();
    $this->plugins();
    $this->configs();
  }

  public function lock(): void
  {
    update_option('vverner_theme-jumpstart', 1, false);
  }

  public function posts(): void
  {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $minimumPostsCount  = 3;
    $total              = 0;
    $uploadDir          = wp_upload_dir();
    $uploadPath         = $uploadDir['path'];

    while ($total < $minimumPostsCount) :
      $postId   = wp_insert_post([
        'post_type'     => 'post',
        'post_status'   => 'publish',
        'post_title'    => 'Demonstrativo 0' . $total,
        'post_content'  => '
        <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Nihil consequatur aspernatur sint, incidunt labore magni nesciunt modi assumenda veritatis deserunt neque ea. Natus, temporibus iste quia ex quod dolor doloremque?</p>
        <p>Libero incidunt animi eaque ad deleniti sunt eligendi at, excepturi asperiores ex velit fuga. Iste nihil neque cum ea officia labore dignissimos repellendus, minima dolorum? Voluptatibus magni temporibus aspernatur labore?</p>
        <p>Beatae, perspiciatis. Ab soluta facilis ad tempora consequuntur voluptatibus praesentium quasi nam doloribus adipisci reiciendis optio, ratione delectus commodi a repellendus eius. Dicta nisi suscipit sequi porro minima autem maiores!</p>'
      ]);

      $placeholder = VV_APP . '/assets/img/placeholder-' . $total . '.jpg';
      $name        = basename($placeholder);
      $file        = $uploadPath . '/' . $name;
      $fileType    = wp_check_filetype($name, null);

      copy($placeholder, $file);

      $imageId  = wp_insert_attachment([
        'post_mime_type' => $fileType['type'],
        'post_title'     => sanitize_file_name($name),
        'post_content'   => '',
        'post_status'    => 'inherit'
      ], $file, $postId);

      $meta = wp_generate_attachment_metadata($imageId, $file);
      wp_update_attachment_metadata($imageId, $meta);
      set_post_thumbnail($postId, $imageId);

      $total++;
    endwhile;
  }

  public function pages(): void
  {
    global $wpdb;
    $sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'vverner_key' AND meta_value = %s";
    $requiredPages = [
      'home'      => 'Início',
      'contact'   => 'Contato',
      'about'     => 'Sobre',
      'blog'      => 'Notícias'
    ];

    foreach ($requiredPages as $key => $page) :
      $pageQuery  = $wpdb->prepare($sql, $key);
      $col          = $wpdb->get_col($pageQuery);

      if ($col) :
        continue;
      endif;

      wp_insert_post([
        'post_type'     => 'page',
        'post_title'    => $page,
        'post_status'   => 'publish',
        'meta_input'    => [
          'vverner_key'   =>  $key
        ]
      ]);
    endforeach;
  }

  public function comments(): void
  {
    global $wpdb;
    $wpdb->delete(
      $wpdb->comments,
      ['comment_post_ID' => '1']
    );
  }

  public function plugins(): void
  {
    $plugins = [
      'contact-form-7',
      'contact-form-cfdb7',
      'wp-smushit',
      'akismet',
      'cookie-law-info'
    ];

    foreach ($plugins as $plugin) :
      $request = new WP_REST_Request('POST', '/wp/v2/plugins');
      $request->set_param('slug', $plugin);
      $request->set_param('status', 'active');

      rest_do_request($request);
    endforeach;
  }

  public function configs(): void
  {
    global $wpdb;

    update_option('blogdescription', '');
    update_option('timezone_string', 'America/Sao_Paulo');
    update_option('date_format', 'd/m/Y');
    update_option('time_format', 'H:i');

    $home = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'vverner_key' AND meta_value = 'home'";
    $blog = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'vverner_key' AND meta_value = 'blog'";

    update_option('show_on_front', 'page');
    update_option('page_on_front', $wpdb->get_var($home));
    update_option('page_for_posts', $wpdb->get_var($blog));
    update_option('posts_per_page', 12);
  }
}

JumpStart::attach();
