<?php

class ValueObject
{
  protected $values = array();
  
  public function __construct($values = null)
  {
    $this->values = (!is_null($values)) ? $values : null;
  }
  
  public static function create()
  {
    return new self();
  }
  
  public function __get($key)
  {
    return (isset($this->values[$key])) ? $this->values[$key] : null;
  }
  
  public function __set($key, $value)
  {
    $this->values[$key] = $value;
  }
}