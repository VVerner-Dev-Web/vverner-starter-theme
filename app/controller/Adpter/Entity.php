<?php

namespace VVerner\Adapter;

use stdClass;

abstract class Entity
{
  public string $TABLE;

  public int $id;

  public function __construct(int $id = null)
  {
    if ($id) :
      $this->load($id);
    endif;
  }

  /**
   * Must be overridden by subclasses
   */
  public static function loadFromDbObject(Entity $cls, stdClass $db)
  {
    return $cls;
  }

  public function save(): bool
  {
    return isset($this->id) ? $this->update() : $this->create();
  }

  public function delete(): void
  {
    global $wpdb;

    $wpdb->delete(
      self::$TABLE,
      ['id' => $this->id],
      ['%d']
    );
  }

  protected function create(): bool
  {
    global $wpdb;

    $created = $wpdb->insert(self::$TABLE, $this->db('value'), $this->db('format'));

    if ($created) :
      $this->id = (int) $wpdb->insert_id;
    endif;

    return $created ? true : false;
  }

  protected function update(): bool
  {
    global $wpdb;

    $updated = $wpdb->update(
      self::$TABLE,
      $this->db('value'),
      ['id' => $this->id],
      $this->db('format'),
      ['%d']
    );

    return $updated ? true : false;
  }

  /**
   * Must be overridden by subclasses
   */
  protected function db(string $returnType = 'value'): array
  {
    return array_map(fn ($item) => $item[$returnType], [
      'origin'       => [
        'value'   => $this->id,
        'format'  => '%d'
      ]
    ]);
  }

  protected function load(int $id): void
  {
    global $wpdb;

    $sql  = "SELECT * FROM " . self::$TABLE . ' WHERE 1 id = ' . $id;
    $data = $wpdb->get_row($sql);

    if ($data) :
      $this->loadFromDbObject($this, $data);
    endif;
  }
}
