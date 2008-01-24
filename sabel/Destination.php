<?php

/**
 * Sabel_Destination
 *
 * @category   Router
 * @package    org.sabel.router
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Destination extends Sabel_Object
{
  private
    $module     = "",
    $controller = "",
    $action     = "";
  
  public function __construct($module, $controller, $action)
  {
    $this->module     = $module;
    $this->controller = $controller;
    $this->action     = $action;
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
  
  public function setModule($module)
  {
    $this->module = $module;
  }
  
  public function setController($controller)
  {
    $this->controller = $controller;
  }
  
  public function setAction($action)
  {
    $this->action = $action;
  }
  
  public function toArray()
  {
    return array($this->module, $this->controller, $this->action);
  }
}
