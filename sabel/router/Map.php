<?php

/**
 * Sabel_Router_Map
 *
 * @category   Router
 * @package    org.sabel.router
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Router_Map implements Sabel_Router
{
  private $candidate   = null;
  private $destination = null;
  
  public function route($request)
  {
    $config = new Map();
    $config->configure();
    
    foreach($config->getRoutes() as $route) {
      $name = $route->getName();
      $uri  = $route->getUri();
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
      
      Sabel_Map_Configurator::addCandidate($name, $uri, $options);
    }
    
    foreach (Sabel_Map_Configurator::getCandidates() as $candidate) {
      if ($candidate->evalute($request->toArray())) {
        Sabel_Context::getContext()->setCandidate($candidate);
        $this->candidate = $candidate;
        return $candidate->getDestination();
      }
    }
    
    $candidate = Sabel_Map_Configurator::getCandidate("default");
    $candidate->setModule("index");
    $candidate->setController("index");
    $candidate->setAction("index");
    Sabel_Context::getContext()->setCandidate($candidate);
    $this->candidate = $candidate;
    
    return $candidate->getDestination();
  }
  
  public function getCandidate()
  {
    return $this->candidate;
  }
}
