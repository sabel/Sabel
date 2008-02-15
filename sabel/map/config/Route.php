<?php

/**
 * Sabel_Map_Config_Route
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
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
  
  public function createDestination()
  {
    $destination = array();
    
    if ($this->module !== "") {
      $destination["module"] = $this->module;
    }
    
    if ($this->controller !== "") {
      $destination["controller"] = $this->controller;
    }
    
    if ($this->action !== "") {
      $destination["action"] = $this->action;
    }
    
    return $destination;
  }
}
