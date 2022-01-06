<?php 

namespace VVerner;

use Exception;

defined('ABSPATH') || exit('No direct script access allowed');

class Files 
{
   private static $instances = [];

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

   public function exists(string $filename): bool
   {
      $file = VV_APP . '/' . $filename;
      return file_exists($file);
   }

   public function createDir(string $dirPath): bool
   {
      $created = false;
      $path    = VV_APP . '/' . $dirPath;

      if (!is_dir($path)) :
         $created = mkdir($path);
         if ($created) : 
            $this->createFile( $dirPath . '/index.php' );
         endif;
      endif;

      return $created;
   }

   public function createFile(string $filename): void
   {
      $path = VV_APP . '/' . $filename;
      if (!file_exists($path)) :
         $handler = fopen( $path, 'a');
         fwrite($handler, '<?php defined(\'ABSPATH\') || exit(\'No direct script access allowed\'); ');
         fclose($handler);
      endif;
   }
}