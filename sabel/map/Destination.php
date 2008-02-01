<?php

/**
 * Sabel_Map_Destination
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Destination extends Sabel_Object
{
  /**
   * @var string
   */
  private $module = "";
  
  /**
   * @var string
   */
  private $controller = "";
  
  /**
   * @var string
   */
  private $action = "";
    
  /**
   * @param array $destination
   *
   * @return boolean
   */
  public function __construct(array $destination)
  {
    $this->module     = $destination["module"];
    $this->controller = $destination["controller"];
    $this->action     = $destination["action"];
  }
  
  /**
   * @return boolean
   */
  public function hasModule()
  {
    return ($this->module !== "");
  }
  
  /**
   * @return boolean
   */
  public function hasController()
  {
    return ($this->controller !== "");
  }
  
  /**
   * @return boolean
   */
  public function hasAction()
  {
    return ($this->action !== "");
  }
  
  /**
   * @return string
   */
  public function getModule()
  {
    return $this->module;
  }
  
  /**
   * @return string
   */
  public function getController()
  {
    return $this->controller;
  }
  
  /**
   * @return string
   */
  public function getAction()
  {
    return $this->action;
  }
  
  /**
   * @param string $module
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return void
   */
  public function setModule($module)
  {
    if (is_string($module)) {
      $this->module = $module;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
  }
  
  /**
   * @param string $controller
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return void
   */
  public function setController($controller)
  {
    if (is_string($controller)) {
      $this->controller = $controller;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
  }
  
  /**
   * @param string $action
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return void
   */
  public function setAction($action)
  {
    if (is_string($action)) {
      $this->action = $action;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be a string.");
    }
  }
  
  /**
   * @return array
   */
  public function toArray()
  {
    return array($this->module, $this->controller, $this->action);
  }
}
