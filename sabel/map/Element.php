<?php

/**
 * Sabel_Map_Element
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Element
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
    return (isset($parts[1])) ? $parts[1] : $parts[0];
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