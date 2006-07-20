<?php

/**
 * Sabel_Controller_Map_Destination
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Controller_Map_Destination
{
  protected $destination = null;
  
  public function __construct($destination)
  {
    $this->destination = $destination;
  }
  
  public function __get($key)
  {
    $methodName = 'get'.ucfirst($key);
    return $this->$methodName();
  }
  
  public function __call($method, $arg)
  {
    $methodName = 'get' . ucfirst($method);
    return $this->$methodName();
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