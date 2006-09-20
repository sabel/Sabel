<?php

class Sabel_DB_Schema_Column
{
  private $data = array();

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    return (isset($this->data[$key])) ? $this->data[$key] : null;
  }

  public function setProperties($array)
  {
    foreach ($array as $key => $val) $this->$key = $val;
  }

  public function make($cols)
  {
    $this->setProperties($cols);
    return $this;
  }
}
