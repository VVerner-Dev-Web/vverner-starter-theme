<?php

namespace VVerner\Adapters;

abstract class Ajax
{
  protected function __construct()
  {
  }

  public static function attach(): void
  {
    $cls = new static;

    if (!defined('VJAX_ATTACHED')) :
      define('VJAX_ATTACHED', true);

      add_action('parse_request', function () {
        if (isset($_REQUEST['vjax']) && $_REQUEST['vjax']) :
          do_action('vverner-ajax/' . $_REQUEST['vjax']);
          exit;
        endif;
      });
    endif;

    $adapterMethods = get_class_methods(__CLASS__);
    $methods = get_class_methods($cls);

    $methods = array_diff($methods, $adapterMethods);

    array_map(fn ($method) => add_action('vverner-ajax/' . self::methodEndpoint($method), [$cls, $method]), $methods);
  }

  protected function validateCapability(string $capability): void
  {
    if (!current_user_can($capability)) :
      $this->response([
        'error' => 'you don\'t have permission to use this feature'
      ]);
    endif;
  }

  protected function validateLogin(): void
  {
    if (!is_user_logged_in()) :
      $this->response([
        'error' => 'you must be logged in to user this feature'
      ]);
    endif;
  }

  protected function validateNonce(string $method): void
  {
    $nonce = isset($_REQUEST['_wpnonce']) && $_REQUEST['_wpnonce'] ? $_REQUEST['_wpnonce'] : '';
    $action = explode('::', $method);
    $action = array_pop($action);

    if (!wp_verify_nonce($nonce, self::methodEndpoint($action))) :
      $this->response(['success' => false, 'error' => 'invalidNonce']);
    endif;
  }

  protected function validateExternalLogin(): void
  {
    if (is_user_logged_in()) :
      return;
    endif;

    add_filter('application_password_is_api_request', '__return_true');

    $auth = getallheaders()['Authorization'] ?? '';
    $auth = explode(':', base64_decode(str_replace('Basic ', '', $auth)));

    if (count($auth) !== 2) :
      $this->response(['error' => 'Authentication failed']);
    endif;

    $user = wp_authenticate_application_password(null, $auth[0], $auth[1]);

    if (!$user || is_wp_error($user)) :
      $this->response(['error' => 'Authentication failed']);
    endif;

    wp_set_current_user($user->ID, $user->user_login);
  }

  protected function response(mixed $data): void
  {
    wp_send_json($data);
  }

  protected function uploadFile(array $file)
  {
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (isset($upload['error'])) :
      return 0;
    endif;

    $filename   = $upload['file'];
    $filetype   = $upload['type'];
    $attachment = [
      'guid'           => WP_CONTENT_URL . '/' . basename($filename),
      'post_mime_type' => $filetype,
      'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
      'post_content'   => '',
      'post_status'    => 'inherit'
    ];

    $id   = wp_insert_attachment($attachment, $filename);
    $meta = wp_generate_attachment_metadata($id, $filename);

    wp_update_attachment_metadata($id, $meta);

    return $id;
  }

  protected function getParam(string $param, int $filter = FILTER_DEFAULT, int $options = 0)
  {
    $value = isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;
    return filter_var($value, $filter, $options);
  }

  private static function methodEndpoint(string $method): string
  {
    $className = explode('\\', static::class);
    $path = array_pop($className);

    $path = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $path)), '-');

    $endpoint = ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $method)), '-');

    return $path . '/' . $endpoint;
  }
}
