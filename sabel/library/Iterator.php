<?php

class Sabel_Library_Iterator
{
  private $array;
  private $count;
  
  public function __construct($array) {
    $this->count = 0;
    $this->array = $array;
  }
  
  public function hasNext()
  {
    return ($this->count < count($this->array));
  }
  
  public function next()
  {
    $value = $this->array[$this->count];
    $this->count++;
    return $value;
  }
  
  public function prev()
  {
    $value = $this->array[$this->count];
    $this->count--;
    return $value;
  }
  
  public function toArray()
  {
    return $this->array;
  }
}

?>