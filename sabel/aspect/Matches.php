<?php

class Sabel_Aspect_Matches implements Iterator
{
  protected $size        = 0;
  protected $position    = 0;
  protected $matches     = array();
  protected $matchesList = array();
  
  public function add($name, $aspect)
  {
    $this->matchesList[$name] = true;
    $this->matches[]          = $aspect;
  }
  
  public function matched($name)
  {
    return (isset($this->matchesList[$name]));
  }
  
  public function hasMatch()
  {
    return (count($this->matches) > 0);
  }
  
  public function current()
  {
    $matches = $this->matches;
    return $matches[$this->position];
  }
  
  public function rewind()
  {
    $this->position = 0;
    $this->size = count($this->matches);
  }
  
  public function valid()
  {
    return ($this->position < $this->size);
  }
  
  public function next()
  {
    $this->position++;
  }
  
  public function key()
  {
    return $this->position;
  }
}