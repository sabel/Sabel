<?php

abstract class Sabel_Map_Config implements Sabel_Config
{
  private $routes = array();
  
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
