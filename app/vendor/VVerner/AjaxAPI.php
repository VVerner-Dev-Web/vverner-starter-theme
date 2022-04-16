<?php 

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class AjaxAPI
{
   private const PREFIX = 'vv_api';

   private static $instances = [];

   protected function __construct() 
   { 
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

   public function getRequestUrl(): string
   {
      return admin_url('admin-ajax.php');
   }

   public function getGlobalNonce(): string
   {
      return wp_create_nonce(self::PREFIX);
   }

   public function getGlobalAction(): string
   {
      return self::PREFIX;
   }

   public function registerPublicRoute(string $route): void
   {
      $route = sanitize_title($route);
      add_action('wp_ajax_nopriv_' . $this->getRoute($route), [$this, 'handleRequest']);
   }

   public function registerPrivateRoute(string $route): void
   {
      $route = sanitize_title($route);
      add_action('wp_ajax_' . $this->getRoute($route), [$this, 'handleRequest']);
   }

   public function handleRequest(): void
   {
      $check1 = check_ajax_referer($_POST['action'], false, false);
      $check2 = wp_verify_nonce($_POST['_wpnonce'], self::PREFIX);

      if (!$check1 && !$check2) : 
         $this->sendError('Incomplete');
      endif;

      $data          = $_POST;
      $route         = str_replace(self::PREFIX, '', $data['action']);

      unset( $data['action'], $data['_wpnonce']);

      if (isset($data['_wp_http_referer'])) : 
         unset($data['_wp_http_referer']);
      endif;

      $response = ['error' => false];

      $response  = apply_filters('vv_api-' . $route, $response, $data);

      if (isset($response['error']) && $response['error']) : 
         $this->sendError($response);
      else : 
         $this->sendSuccess($response);
      endif;
   }

   public function getApiMandatoryFields(string $route): string
   {
      $route = $this->getRoute($route);
      $html  = '';
      $html .= '<input name="action" value="'. $route .'" type="hidden" readonly>';
      $html .= wp_nonce_field($route, '_wpnonce', true, false);
      return $html;
   }

   private function getRoute(string $route): string
   {
      return self::PREFIX . '/' . $route;
   }

   private function sendError($data): void
   {
      $data = is_array($data) ? $data : ['message' => $data];
      $data['success'] = false;
      
      $this->respond($data, 400);
   }

   private function sendSuccess($data): void
   {
      $data = is_array($data) ? $data : ['message' => $data];
      $data['success'] = true;
      
      $this->respond($data, 200);
   }

   private function respond(array $data, int $code = 200): void
   {
      wp_send_json($data, $code);
   }
}