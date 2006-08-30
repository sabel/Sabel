<?php

/**
 * Sabel_DB_Schema_Column
 *
 * @package org.sabel.db.schema
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Sabel_DB_Schema_Column
{
  protected $data = array();

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    return $this->data[$key];
  }
}
