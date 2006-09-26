<?php

/**
 * Sabel_Map_Destination
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Map_Destination
{
  const MODULE     = 'module';
  const CONTROLLER = 'controller';
  const ACTION     = 'action';
  
  protected $destination = array();
  
  public function __construct($dest = null)
  {
    $this->destination = (!is_null($dest)) ? $dest : null;
  }
  
  public function mappingByRequest($mapUri, $requestUri)
  {
    if (!is_object($mapUri)) {
      throw new Sabel_Exception_Runtime('MapUri is not object.');
    }
    
    if (!is_object($requestUri)) {
      throw new Sabel_Exception_Runtime('requestUri is not object.');
    }
    
    $dest = $this->destination;
    
    $elems = array(new Sabel_Map_Element($dest[self::MODULE]),
                   new Sabel_Map_Element($dest[self::CONTROLLER]),
                   new Sabel_Map_Element($dest[self::ACTION]));
                   
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
  
  public function setDestination($destination)
  {
    $this->destination = $destination;
  }
  
  public function setModule($name)
  {
    $this->destination[self::MODULE] = $name;
  }
  
  public function setController($name)
  {
    $this->destination[self::CONTROLLER] = $name;
  }
  
  public function setAction($name)
  {
    $this->destination[self::ACTION] = $name;
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