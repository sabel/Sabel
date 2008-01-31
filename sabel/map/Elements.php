<?php

class Sabel_Map_Elements extends Sabel_Object implements Countable
{
  protected $components = array();
  
  public function __construct(array $elements)
  {
    $this->components = $elements;
  }
  
  public function hasDefaults()
  {
    foreach ($this->components as $element) {
      if ($element->hasDefault()) return true;
    }
    return false;
  }
  
  public function appendToRequests(&$requests)
  {
    foreach ($this->components as $element) {
      if ($element->hasDefault()) {
        $requests[] = $element->default;
      }
    }
  }
  
  public function hasConstant()
  {
    return $this->components[0]->isConstant();
  }
  
  public function matchToConstants($requests)
  {
    for ($i = 0, $c = $this->count(); $i < $c; ++$i) {
      $element = $this->components[$i];
      if ($element->isConstant()) {
        if (!isset($requests[$i]) || !$element->equalsTo($requests[$i])) {
          return false;
        }
      }
    }
    
    return true;
  }
  
  public function getNext()
  {
    $next = key($this->components) + 1;
    
    if (isset($this->components[$next])) {
      return $this->components[$next];
    } else {
      return null;
    }
  }
  
  public function hasArray()
  {
    return $this->components[$this->count() - 1]->isArray();
  }
    
  public function count()
  {
    return count($this->components);
  }
  
  public function get($index)
  {
    if (isset($this->components[$index])) {
      return $this->components[$index];
    } else {
      return null;
    }
  }
}