<?php

class Column
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

