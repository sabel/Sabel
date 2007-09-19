<?php

class Sabel_View_Repository extends Sabel_Object
{
  private $resources = array();
  
  public function get($module, $controller, $action)
  {
    return $this->resources[$module][$controller][$action];
  }
  
  public function create($module, $controller, $action)
  {
    $this->resources[$module][$controller][$action] = "";
  }
  
  public function add($module, $controller, $action)
  {
    $this->resources[$module][$controller][$action] = "";
  }
}