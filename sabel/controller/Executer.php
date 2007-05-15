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
  
  public function __construct($destination)
  {
    $this->destination = $destination;
  }
  
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
  
  public function execute($request, $storage)
  {
    $controller = $this->controller;
    $action = $this->destination->getAction();
    
    $controller->setup($request, $storage);
    $controller->setAction($action);
    $controller->initialize();
    $controller->execute($action);
  }
}
