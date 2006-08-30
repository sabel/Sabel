<?php

/**
 * Sabel_DB_Column
 *
 * @package org.servise.db
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_DB_Column
{
  protected $table     = '';
  protected $name      = '';
  protected $localName = '';
  protected $value     = '';

  public function __construct($values, $table)
  {
    $this->name      = $values['name'];
    $this->localName = _("{$table}.{$this->name}");
    $this->value     = $values['data'];
    $this->table     = $table;
  }

  public function __get($key)
  {
    return $this->$key;
  }
}
