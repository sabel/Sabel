<?php

/**
 * Sabel_Controller_Map_Uri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Uri
{
  protected $uri;
  
  public function __construct($uri)
  {
    $this->uri = $uri;
  }
  
  public function getString()
  {
    return (string) $this->uri;
  }
  
  public function count()
  {
    return count(explode('/', $this->uri));
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