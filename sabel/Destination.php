<?php

class Sabel_Destination
{
  private
    $module     = "",
    $controller = "",
    $action     = "";
  
  public function __construct($module, $controller, $action)
  {
    $this->module = $module;
    $this->controller = $controller;
    $this->action = $action;
  }
  
  public function hasModule()
  {
    return ($this->module !== "");
  }
  
  public function hasController()
  {
    return ($this->controller !== "");
  }
  
  public function hasAction()
  {
    return ($this->action !== "");
  }
  
  public function getModule()
  {
    return $this->module;
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function toArray()
  {
    return array($this->module, $this->controller, $this->action);
  }
}