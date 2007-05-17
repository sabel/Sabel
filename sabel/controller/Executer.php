<?php

/**
 * Sabel_Controller_Executer
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Executer
{
  private $controller = null;
  private $destination = null;
  
  /**
   * default consturcter
   *
   * @param Sabel_Destination $destination
   */
  public function __construct($destination)
  {
    if (! $destination instanceof Sabel_Destination) {
      $msg  = "call without require argument ";
      $msg .= "Sabel_Controller_Executer::__construct(arg)";
      $msg .= " arg must be Sabel_Destination";
      throw new Sabel_Exception_Runtime($msg);
    }
    
    $this->destination = $destination;
  }
  
  /**
   * create controller instance
   *
   * @return a subclass instance of Sabel_Controller_Page
   */
  public function create()
  {
    list($module, $controller,) = $this->destination->toArray();
    
    $classpath = null;
    $instance  = null;
    
    $classpath  = $module;
    $classpath .= '_' . trim(Sabel_Const::CONTROLLER_DIR, '/');
    
    if ($controller !== "") {
      $classpath .= '_' . ucfirst($controller);
    } else {
      $classpath .= '_' . ucfirst(Sabel_Const::DEFAULT_CONTROLLER);
    }
    
    if (class_exists($classpath)) {
      $instance = new $classpath();
    } else {
      $instance = new Index_Controllers_Index();
    }
    
    Sabel_Context::setPageController($instance);
    $this->controller = $instance;
    
    return $instance;
  }
  
  /**
   * execute an action
   *
   * @param Sabel_Request $request
   * @param Sabel_Storage $storage
   */
  public function execute($request, $storage)
  {
    $controller = $this->controller;
    $action = $this->destination->getAction();
    
    $controller->setup($request, $storage);
    $controller->setAction($action);
    $controller->initialize();
    
    $this->executeAction($action);
  }
  
  protected function executeAction($action)
  {
    $this->controller->execute($action);
  }
  
  protected function getController()
  {
    return $this->controller;
  }
  
  protected function setActionToDestination($action)
  {
    $this->destination->setAction($action);
  }
}
