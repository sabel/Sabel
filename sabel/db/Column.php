<?php

/**
 * Sabel_DB_Column
 *
 * @package org.servise.db
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_DB_Column
{
  protected $name      = '';
  protected $localName = '';
  protected $data      = '';
  protected $table     = '';
  
  public function __construct($values, $table)
  {
    $this->name      = $values['name'];
    $this->localName = _("{$table}.{$this->name}");
    $this->data      = $values['data'];
    $this->table     = $table;
  }
  
  public function __get($key)
  {
    return $this->$key;
  }
}