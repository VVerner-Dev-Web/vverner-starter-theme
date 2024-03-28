<?php

namespace VVerner\Core;

class Database
{
  private readonly array $tables;

  private const ACTION_PREFIX = 'vvtheme/db/';

  private function __construct()
  {
    $this->tables = [];
  }

  public static function attach(): void
  {
    $cls = new self();
    add_action('init', $cls->updateDb(...), PHP_INT_MAX);
  }

  public function updateDb(): void
  {
    foreach ($this->tables as $table => $currentVersion) :
      $table   = implode('', array_map('ucfirst', explode('_', $table)));
      $siteVersion = get_option(self::ACTION_PREFIX . $table, '0.0.0');
      if (version_compare($currentVersion, $siteVersion, '>')) :

        do_action(self::ACTION_PREFIX . 'upgrade/before', $table, $currentVersion);

        $updated = call_user_func([$this, 'update' . $table], $table);

        do_action(self::ACTION_PREFIX . 'upgrade/after', $table, $currentVersion);

        if ($updated) :
          update_option(self::ACTION_PREFIX . $table, $currentVersion, false);
        endif;
      endif;
    endforeach;
  }
}
