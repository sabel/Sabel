<?php

class Sabel_Library_ArrayList implements Iterator
{
  private $array;
  private $position = 0;
  private $size = 0;
  
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
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    return $this->array[$this->position];
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function key()
  {
    return $this->position;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function next()
  {
    return $this->position++;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function rewind()
  {
    $this->size = count($this->array);
    $this->position = 0;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function valid()
  {
    return ($this->position < $this->size);
  }
}