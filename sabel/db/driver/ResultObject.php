<?php

class Sabel_DB_Driver_ResultObject
{
  private $data = array();

  public function __construct($assocRow)
  {
    if (!is_array($assocRow))
      throw new Exception('Error: ResultObject::__construct() argument must be an array.');

    foreach ($assocRow as $column => $value) $this->data[$column] = $value;
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    $data = $this->data[$key];
    return (is_numeric($data)) ? (int)$data : $data;
  }

  public function toArray()
  {
    return $this->data;
  }
}

