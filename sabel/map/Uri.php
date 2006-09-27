<?php

/**
 * Sabel_Map_Uri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Uri implements Iterator
{
  protected $uri = '';
  protected $elements = array();
  private $position = 0;
  private $limit = 0;
  
  public function __construct($uri)
  {
    $this->uri      = $uri;
    $this->elements = explode('/', $uri);
    $this->limit    = count($this->elements);
  }
  
  public function getString()
  {
    return (string) $this->uri;
  }
  
  public function count()
  {
    return count($this->elements);
  }
  
  public function calcElementPositionByName($name)
  {
    $position = 0;
    foreach ($this->elements as $element) {
      $oElement = new Sabel_Map_Element($element);
      if ($oElement->getName() === $name) return $position;
      $position++;
    }
  }
  
  public function getElement($position)
  {
    $position =(int) $position;
    
    if (0 <= $position && $position < $this->limit) {
      return new Sabel_Map_Element($this->elements[$position]);
    } else {
      return false;
    }
  }
  
  public function getElements()
  {
    $objElements = array();
    foreach ($this->elements as $element) {
      $objElements[] = new Sabel_Map_Element($element);
    }
    return $objElements;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    return $this->getElement($this->position);
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
    return ($this->position < $this->limit);
  }
}