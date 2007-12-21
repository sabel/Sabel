<?php

/**
 * Sabel_Map_Config
 *
 * @abstract
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
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
