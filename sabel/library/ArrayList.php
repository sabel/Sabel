<?php

uses('sabel.library.Iterator');

class Sabel_Library_ArrayList
{
  private $array;
  
  public function __construct($array = null) {
    if ($array) $this->array = $array;
  }
  
  public function push($value)
  {
    $this->array[] = $value;
  }
  
  public function pop()
  {
    return array_pop($this->array);
  }
  
  public function set($key, $value) {
    $this->array[$key] = $value;
  }
  
  public function get($key)
  {
    return $this->array[$key];
  }
  
  public function iterator()
  {
    return new Sabel_Library_Iterator($this->array);
  }
  
  public function toArray()
  {
    return $this->array;
  }
}

?>