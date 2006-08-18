<?php

/**
 * Sabel_Controller_Map_Uri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Uri implements Iterator
{
  protected $uri;
  private $position = 0;
  private $limit = 0;
  
  public function __construct($uri)
  {
    $this->uri = $uri;
    $this->limit = $this->count();
  }
  
  public function getString()
  {
    return (string) $this->uri;
  }
  
  public function count()
  {
    return count(explode('/', $this->uri));
  }
  
  public function calcElementPositionByName($name)
  {
    $position = 0;
    $elements = explode('/', $this->uri);
    
    foreach ($elements as $element) {
      $oElement = new Sabel_Controller_Map_Element($element);
      if ($oElement->getName() == $name) return $position;
      $position++;
    }
  }
  
  public function getElement($position)
  {
    $elements = explode('/', $this->uri);
    if (0 <= $position && $position < count($elements)) {
      return new Sabel_Controller_Map_Element($elements[$position]);
    } else {
      return false;
    }
  }
  
  public function getElements()
  {
    $elements = explode('/', $this->uri);
    $objElements = array();
    foreach ($elements as $element) {
      $objElements[] = new Sabel_Controller_Map_Element($element);
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

/**
 * ClassName
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Element
{
  protected $element;
  
  public function __construct($element)
  {
    $this->element = $element;
  }
  
  public function get()
  {
    $this->element;
  }
  
  public function getName()
  {
    $parts = explode(':', $this->element);
    return $parts[1];
  }
  
  public function isConstant()
  {
    return (strpos($this->element, ':') === false);
  }
  
  public function getConstant()
  {
    return ($this->isConstant()) ? $this->element : false;
  }
  
  public function isReservedWord()
  {
    if ($this->isModule() || $this->isController() || $this->isAction()) {
      return true;
    } else {
      return false;
    }
  }
  
  public function isModule()
  {
    return ($this->element === ':module');
  }
  
  public function isController()
  {
    return ($this->element === ':controller');
  }
  
  public function isAction()
  {
    return ($this->element === ':action');
  }
  
  public function toString()
  {
    return $this->element;
  }
}