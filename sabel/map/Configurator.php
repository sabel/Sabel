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
    foreach ($this->routes as $route) {
      $name = $route->getName();
      $candidate = new Sabel_Map_Candidate($name);
      $candidate->route($route->getUri())->setOptions($this->buildOptions($route));
      $this->candidates[$name] = $candidate;
    }
    
    return $this->candidates;
  }
  
  public function buildOptions(Sabel_Map_Config_Route $route)
  {
    $options = array();
    
    if ($route->hasModule()) {
      $options["module"] = $route->getModule();
    }
    
    if ($route->hasController()) {
      $options["controller"] = $route->getController();
    }
    
    if ($route->hasAction()) {
      $options["action"] = $route->getAction();
    }
    
    $options["default"]     = $route->getDefaults();
    $options["requirement"] = $route->getRequirements();
    
    return $options;
  }
  
  public function getCandidate($name)
  {
    if (isset($this->candidates[$name])) {
      return $this->candidates[$name];
    } else {
      return false;
    }
  }
  
  public function getCandidates()
  {
    return $this->candidates;
  }
  
  public function setCandidates($candidates)
  {
    $this->candidates = $candidates;
  }
  
  public function reset()
  {
    $this->candidates = array();
  }
}
