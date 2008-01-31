<?php

/**
 * Map Configurator
 * useful interface of Sabel_Map_Candidate
 *
 * @abstract
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Map_Configurator implements Sabel_Config
{
  protected $routes = array();
  protected $candidates = array();
  
  public function route($name)
  {
    return $this->routes[] = new Sabel_Map_Config_Route($name);
  }
  
  public function build()
  {
    $candidates = array();
    foreach ($this->routes as $route) {
      $name = $route->getName();
      $candidate = new Sabel_Map_Candidate($name);
      $candidate->route($route);
      $candidates[$name] = $candidate;
    }
    
    Sabel_Context::getContext()->setCandidates($candidates);
    
    return $candidates;
  }
  
  public function reset()
  {
    $this->candidates = array();
  }
}
