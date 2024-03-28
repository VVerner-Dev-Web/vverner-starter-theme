<?php

namespace VVerner\Core;

class Schema
{
  public string $table;
  public array $fields = [];
  private string $sqlFields;

  public function __construct(string $table)
  {
    $this->table = $table;
  }

  public function createIfNotExists(): bool
  {
    global $wpdb;

    $this->fieldsToSql();
    $sql = "CREATE TABLE IF NOT EXISTS {$this->table} ({$this->sqlFields})";
    return $wpdb->query($sql);
  }

  private function fieldsToSql(): void
  {
    $length = count($this->fields);

    foreach ($this->fields as $key => $field) :
      $this->sqlFields .= $key . ' ' . $field['type'];

      if (array_key_exists('primaryKey', $field)) :
        $this->sqlFields .= " PRIMARY KEY";
      endif;

      if (array_key_exists('unique', $field)) :
        $this->sqlFields .= " UNIQUE";
      endif;

      if (array_key_exists('unsigned', $field)) :
        $this->sqlFields .= " UNSIGNED";
      endif;

      if (!array_key_exists('nullable', $field)) :
        $this->sqlFields .= " NOT NULL";
      endif;

      if (array_key_exists('autoIncrement', $field)) :
        $this->sqlFields .= " AUTO_INCREMENT";
      endif;
      if ($length >  1) :
        $length--;
        $this->sqlFields .= ', ';
      endif;

    endforeach;
    $this->addConstraints();
  }

  private function addConstraints(): void
  {
    foreach ($this->fields as $key => $field) :

      if (!array_key_exists('foreign', $field)) {
        continue;
      }

      $this->sqlFields .= ", CONSTRAINT FK_{$this->table}_" . $field['foreign']['table'] . " FOREIGN KEY ({$key}) ";
      $this->sqlFields .= " REFERENCES " . $field['foreign']['table'] . '(' . $field['foreign']['column'] . ')';

    endforeach;
  }

  public function addColumn(string $name, string $type): Schema
  {
    $this->fields[$name] = [
      'type' => $type
    ];

    return $this;
  }

  public function primaryKey(string $name): Schema
  {

    $this->fields[$name]['primaryKey'] = true;

    return $this;
  }

  public function nullable(string $name): Schema
  {

    $this->fields[$name]['nullable'] = true;

    return $this;
  }

  public function unique(string $name): Schema
  {
    $this->fields[$name]['unique'] = true;

    return $this;
  }

  public function unsigned(string $name): Schema
  {
    $this->fields[$name]['unsigned'] = true;

    return $this;
  }

  public function autoIncrement(string $name): Schema
  {
    $this->fields[$name]['autoIncrement'] = true;

    return $this;
  }

  public function foreignKey(string $name, string $referenceTable, string $refereceColumn): Schema
  {
    $this->fields[$name]['foreign'] = [
      'table' => $referenceTable,
      'column' => $refereceColumn
    ];

    return $this;
  }

  public function dropIfExists(): void
  {
    global $wpdb;

    $sql = "DROP TABLE IF EXISTS {$this->table}";
    $wpdb->query($sql);
  }

  public function alter(string $action): Schema
  {
    global $wpdb;

    $this->fieldsToSql();
    $sql = "ALTER TABLE {$this->table}";

    switch ($action):
      case 'add':
        $sql .= " ADD COLUMN {$this->sqlFields}";
        break;

      case 'drop':
        $column = array_key_first($this->fields);
        $sql .= " DROP COLUMN {$column}";
        break;

      case 'alter':
        $sql .= " MODIFY COLUMN {$this->sqlFields}";
        break;
    endswitch;
    $wpdb->query($sql);

    return $this;
  }

  public function resetFields(): Schema
  {
    $this->fields = [];
    return $this;
  }
}
