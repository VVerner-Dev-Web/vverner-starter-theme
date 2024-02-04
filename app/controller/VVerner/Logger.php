<?php

namespace VVerner;

use DateTime;

class Logger
{
	private static $logger = [];

	private static $dir = '/vv-logs/';

	public static function getInstace(): Logger
	{
		$cls = static::class;
    if (!isset(self::$logger[$cls])) :
      self::$logger[$cls] = new static();
    endif;

    return self::$logger[$cls];
	}

	public function showMessage(string $message):void
	{
    error_log('=== VV_LOG ===');
		error_log($message);
	}

	public function showVar($var):void
	{
    error_log('=== VV_LOG ===');
		error_log(print_r($var, true));
	}

	 public function showInSite($thing): void
  {
		$app = App::getInstance();
    if ($app->isVVernerUser()) :
      add_action('the_content', function () use ($thing) {
        echo '<pre>';
        var_dump($thing);
        echo '<pre>';
      });
    endif;
  }

	public function createLogFile(string $filename = 'vv-log'): void
	{
		$files = Files::getInstance();
		$created = false;
		
		if(!$files->exists(self::$dir)):
			$created = $files->createDir(self::$dir);
			if(!$created):
				$this->showMessage('Não foi criado a pasta');
				return;
			endif;
		endif;

		if($files->exists(self::$dir . $filename . '.log')):
			return;
		endif;
		

		$date = new DateTime();
		$handler = fopen($this->getFullPath() . $filename . '.log', 'a');
		fwrite($handler, '[' . $date->format('Y-m-d H:i:s') . "] - Log Created \n");
		fclose($handler);
	}

	private function getFullPath(): string
	{
		return VV_APP . self::$dir;
	}

	public function writeLog($file, $message): void
	{
		$file = $this->getFullPath() . $file . '.log';
		$date = new DateTime();

		$handler = fopen($file, 'a');
		fwrite($handler,'[' . $date->format('Y-m-d H:i:s') . '] - ' . print_r($message, true) . " \n");
		fclose($handler);
	}
}