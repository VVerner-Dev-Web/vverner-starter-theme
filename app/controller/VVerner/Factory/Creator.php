<?php 

namespace VVerner\Factory;

use Exception;

abstract class Creator
{
	private const DIR_PATH = VV_APP .'/controller/VVerner/Factory/Classes/';

	public static function factoryMethod(string $type, $data)
	{
		$classes = self::getTypes();
		return in_array($type, $classes) ? new $type($data) : throw new Exception('Class not exists');
	}

	public static function getTypes():array
	{
		$classes = scandir(self::DIR_PATH);
		return array_map(function($file) {	
			if($file !== '.' && $file !== '..'):
				return preg_replace('/[.php]/', '', $file);
			endif;
		}, $classes);
	}

	public static function requireClasses(): void
	{
		$classes = scandir(self::DIR_PATH);
		array_map(function($file) {	
			if($file !== '.' && $file !== '..'):
				require self::DIR_PATH . $file;	
			endif;
		}, $classes);
	}
}