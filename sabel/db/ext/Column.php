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

  public function __construct($values, $columns, $table)
  {
    $this->name      = $values['name'];
    $this->localName = _("{$table}.{$this->name}");
    $this->value     = $values['data'];
    $this->table     = $table;
    $this->columns   = $columns[$values['name']];
  }

  public function __get($key)
  {
    return $this->$key;
  }
  
  public function getType()
  {
    return $this->columns->type;
  }
  
  public function __toString()
  {
    return $this->value;
  }
}