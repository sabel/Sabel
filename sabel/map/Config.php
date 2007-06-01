<?php

abstract class Sabel_Map_Config
{
  private $routes = array();
  
  abstract function configure();
  
  public function route($name)
  {
    $route = new Sabel_Map_Config_Route($name);
    $this->routes[$name] = $route;
    
    return $route;
  }
  
  public function getRoutes()
  {
    return $this->routes;
  }
}

class Sabel_Map_Config_Route
{
  private $name = "";
  private $uri  = "";
  private $requirements = array();
  private $defaults = array();
  
  private $module = "", $controller = "", $action = "";
  
  public function __construct($name)
  {
    $this->name = $name;
  }
  
  public function uri($uri)
  {
    $this->uri = $uri;
    return $this;
  }
  
  public function requirements($requirements)
  {
    $this->requirements = $requirements;
    return $this;
  }
  
  public function defaults($defaults)
  {
    $this->defaults = $defaults;
    return $this;
  }
  
  public function module($module)
  {
    $this->module = $module;
    return $this;
  }
  
  public function controller($controller)
  {
    $this->controller = $controller;
    return $this;
  }
  
  public function action($action)
  {
    $this->action = $action;
    return $this;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getModule()
  {
    return $this->module;
  }
  
  public function hasModule()
  {
    return ($this->module !== "");
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function hasController()
  {
    return ($this->controller !== "");
  }
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function hasAction()
  {
    return ($this->action !== "");
  }
  
  public function getUri()
  {
    return $this->uri;
  }
  
  public function getRequirements()
  {
    return $this->requirements;
  }
  
  public function getDefaults()
  {
    return $this->defaults;
  }
}
