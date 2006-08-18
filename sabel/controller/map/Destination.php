<?php

/**
 * Sabel_Controller_Map_Destination
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Destination
{
  protected $parentEntry = null;
  protected $destination = array();
  
  public function __construct($entry, $destination)
  {
    $this->parentEntry = $entry;
    
    $uri  = $entry->getUri();
    $ruri = $entry->getRequestUri();
    
    for ($pos = 0; $pos < $uri->count(); $pos++) {
      $element = $uri->getElement($pos);
      if ($element->isModule()) {
        $destElement = new Sabel_Controller_Map_Element($destination['module']);
        if ($destElement->isModule()) $destination['module'] = $ruri->get($pos);
      } else if ($element->isController()) {
        $destElement = new Sabel_Controller_Map_Element($destination['controller']);
        if ($destElement->isController()) $destination['controller'] = $ruri->get($pos);
      } else if ($element->isAction()) {
        $destElement = new Sabel_Controller_Map_Element($destination['action']);
        if ($destElement->isAction()) $destination['action'] = $ruri->get($pos);
      }
    }
    
    $this->destination = $destination;
  }
  
  public function __get($key)
  {
    $methodName = 'get' . ucfirst($key);
    return $this->$methodName();
  }
  
  public function __call($method, $arg)
  {
    $methodName = 'get' . ucfirst($method);
    return $this->$methodName();
  }
  
  public function getEntry()
  {
    return $this->parentEntry;
  }
  
  public function hasModule()
  {
    return (isset($this->destination['module']));
  }
  
  public function hasController()
  {
    return (isset($this->destination['controller']));
  }
  
  public function hasAction()
  {
    return (isset($this->destination['action']));
  }
  
  public function getModule()
  {
    return $this->destination['module'];
  }
  
  public function getController()
  {
    return $this->destination['controller'];
  }
  
  public function getAction()
  {
    return $this->destination['action'];
  }
  
  public function toArray()
  {
    return $this->destination;
  }
}