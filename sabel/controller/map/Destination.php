<?php

/**
 * Sabel_Controller_Map_Destination
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Destination
{
  const MODULE     = 'module';
  const CONTROLLER = 'controller';
  const ACTION     = 'action';
  
  protected $parentEntry = null;
  protected $destination = array();
  
  public function __construct($entry, $dest)
  {
    $this->parentEntry = $entry;
   
    $mapUri     = $entry->getUri();
    $requestUri = $entry->getRequestUri();
    
    $elems = array(new Sabel_Controller_Map_Element($dest[self::MODULE]),
                   new Sabel_Controller_Map_Element($dest[self::CONTROLLER]),
                   new Sabel_Controller_Map_Element($dest[self::ACTION]));
      
    $pos = 0;
    foreach ($mapUri as $element) {
      switch (true) {
        case ($element->isModule() && $elems[0]->isModule()):
          $dest[self::MODULE] = $requestUri->getUri()->get($pos);
          break;
        case ($element->isController() && $elems[1]->isController()):
          $dest[self::CONTROLLER] = $requestUri->getUri()->get($pos);
          break;
        case ($element->isAction() && $elems[2]->isAction()):
          $dest[self::ACTION] = $requestUri->getUri()->get($pos);
          break;
      }
      
      $pos++;
    }
    
    $this->destination = $dest;
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