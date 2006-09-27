<?php

/**
 * Sabel_Map_Requirements
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Requirements implements Iterator
{
  protected $position = 0;
  protected $requirements = array();
  
  public function __construct()
  {
  }
  
  public function hasRequirements()
  {
    return (count($this->requirements) > 0) ? true : false;
  }
  
  public function setRequirement($name, $rule)
  {
    $this->requirements[$name] = new Sabel_Map_Requirement($rule);
  }
  
  public function get($position)
  {
    $reqs = array_values($this->requirements);
    if (isset($reqs[$position])) {
      return $reqs[$position];
    } else {
      return false;
    }
  }
  
  public function getByName($name)
  {
    return $this->requirements[$name];
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    $reqs = array_values($this->requirements);
    return $reqs[$this->position];
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
    $this->position = 0;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function valid()
  {
    return ($this->position < count($this->requirements));
  }
}