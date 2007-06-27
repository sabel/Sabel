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
class Sabel_Controller_Executer_Basic
{
  private
    $context     = null,
    $request     = null,
    $controller  = null,
    $destination = null;
    
  public function setContext($context)
  {
    $this->context = $context;
    $context->getPlugin()->setExecuter($this);
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
    
    $classpath  = ucfirst($module);
    $classpath .= "_" . ucfirst(trim(Sabel_Const::CONTROLLER_DIR, "/"));
    
    if ($controller !== "") {
      $classpath .= "_" . ucfirst($controller);
    } else {
      $classpath .= "_" . ucfirst(Sabel_Const::DEFAULT_CONTROLLER);
    }
    
    if (class_exists($classpath)) {
      $instance = new $classpath($this->context);
    } else {
      throw new Sabel_Exception_Runtime("controller not found");
    }
    
    $this->controller = $instance;
    
    $plugin = $this->context->getPlugin();
    $plugin->onCreateController($this->destination);
    $plugin->setController($instance);
    $this->context->setController($this->controller);
    
    return $instance;
  }
  
  /**
   * set an instance of destination
   *
   * @access public
   * @param Sabel_Destination $destination
   * @throws Sabel_Exception_Runtime
   */
  public function setDestination($destination)
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
   * execute an action.
   *
   * @param Sabel_Request $request
   * @param Sabel_Storage $storage
   * @return void
   */
  public function execute($request, $storage, $plugin = true)
  {
    $this->request = $request;
    $controller    = $this->controller;
    $action        = $this->destination->getAction();
    
    $controller->setup($request, $this->destination, $storage);
    $controller->setAction($action);
    $controller->initialize();
    
    return $this->executeAction($action, $plugin);
  }
  
  /**
   * execute an action.
   *
   * @access protected
   * @param string $action the name of action.
   * @return mixed result of controller
   */
  protected function executeAction($action, $pluginUse)
  {
    l("[Core::Executer] executeAction {$action}");
    
    $plugin = $this->context->getPlugin();
    
    if ($pluginUse === false) {
      return $this->controller->execute($action);
    }
    
    if ($plugin->hasExecuteAction()) {
      $plugin->setDestination($this->destination);
      return $plugin->onExecuteAction($action);
    } else {
      return $this->controller->execute($action);
    }
  }
  
  protected function getController()
  {
    return $this->controller;
  }
  
  protected function getRequest()
  {
    return $this->request;
  }
  
  protected function setActionToDestination($action)
  {
    $this->destination->setAction($action);
  }
}
