<?php

namespace Vverner;

use VVerner\Schema;

class Database
{
	private array $tables;

	private const SITE_PREFIX = 'vvtheme';

	private function __construct()
	{
		$this->tables = [
			'users_2' => '0.0.1'
		];
	}

	public static function attach()
	{
		$cls = new self();
		add_action('init',[$cls, 'updateDb'], PHP_INT_MAX);
	}

	public function updateDb(): void
  {
    foreach ($this->tables as $table => $currentVersion) :
      $table   = implode('', array_map('ucfirst', explode('_', $table)));
      $siteVersion = get_option( self::SITE_PREFIX . '/db/' . $table, '0.0.0');
      if (version_compare($currentVersion, $siteVersion, '>')) :
        $updated = call_user_func([$this, 'update' . $table], $table);

        do_action(self::SITE_PREFIX . '/db/upgraded', $table, $currentVersion);

        if ($updated) :
          update_option(self::SITE_PREFIX . '/db/' . $table, $currentVersion, false);
        endif;
      endif;
    endforeach;
  }
	
	public function updateUsers2(string $tableName)
	{
		$schema = new Schema($tableName);
		
		return $schema->addColumn('id', 'bigint')
					 ->addColumn('name', 'text')
					 ->createIfNotExists();
	}
}

// Database::attach();