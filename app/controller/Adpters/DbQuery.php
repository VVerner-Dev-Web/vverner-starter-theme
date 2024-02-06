<?php

namespace VVerner\Adapters;

use stdClass;
use VVerner\App;

abstract class DbQuery
{
  const RESULTS_PER_PAGE = 30;

  protected Entity $cls;

  private string $select = '*';
  private string $from = '';
  private string $where = '1';
  private string $orderby = 'id';
  private string $order = 'DESC';

  public function __construct()
  {
  }

  public function select(string $select): self
  {
    $this->select = $select;
    return $this;
  }

  public function from(string $from): self
  {
    $this->from = $from;
    return $this;
  }

  public function where(string $where): self
  {
    $this->where = $where;
    return $this;
  }

  public function orderby(string $orderby): self
  {
    $this->orderby = $orderby;
    return $this;
  }

  public function order(string $order): self
  {
    $this->order = $order;
    return $this;
  }

  public function fetchAll(): array
  {
    $sql = "SELECT {$this->select} FROM " . $this->dbName() . " WHERE {$this->where} ORDER BY {$this->orderby} {$this->order}";
    return  $this->fetch($sql);
  }

  public function fetchOne(): mixed
  {
    $sql     = "SELECT {$this->select} FROM " . $this->dbName() . " WHERE {$this->where} ORDER BY {$this->orderby} {$this->order} LIMIT 1";
    $results = $this->fetch($sql);

    return array_shift($results);
  }

  public function fetchPage(int $currentPage): stdClass
  {
    $pagination = (object) [
      'currentPage'   => $currentPage,
      'totalPages'    => 0,
      'nextPage'      => null,
      'previousPage'  => $currentPage > 1 ? $currentPage - 1 : null,
    ];

    $sql = "SELECT COUNT(*) FROM " . $this->dbName() . " WHERE {$this->where}";
    $totalItems = (int) $this->fetch($sql)[0];

    $pagination->totalPages = max(1, ceil($totalItems / self::RESULTS_PER_PAGE));
    $pagination->nextPage = $currentPage >= $pagination->totalPages ? null : 1 + $currentPage;

    $offset = self::RESULTS_PER_PAGE * ($currentPage - 1);
    $limit  = "LIMIT $offset," . self::RESULTS_PER_PAGE;

    $sql  = str_replace('COUNT(*)', '*', $sql);
    $sql .= " ORDER BY {$this->orderby} {$this->order} $limit";

    $results = $this->fetch($sql);

    return (object) compact('results', 'pagination');
  }

  private function fetch(string $sql)
  {
    global $wpdb;

    if (App::isDevMode()) :
      error_log($sql);
    endif;

    $results = $this->fetchingCol() ? $wpdb->get_col($sql) : $wpdb->get_results($sql);

    if ('*' === $this->select && isset($this->cls)) :
      foreach ($results as $index => $row) :
        $results[$index] = $this->cls::loadFromDbObject(new $this->cls, $row);
      endforeach;
    endif;

    return $results;
  }

  private function fetchingCol(): bool
  {
    return '*' !== $this->select && count(explode(',', $this->select)) === 1;
  }

  private function dbName(): string
  {
    global $wpdb;
    $db = $wpdb->posts;

    if (isset($this->cls)) :
      $db = $this->cls::TABLE;
    elseif (isset($this->from) && $this->from) :
      $db = $this->from;
    endif;

    return $db;
  }
}
