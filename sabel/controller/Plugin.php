<?php

/**
 * Manage and Execute plugin of page controller
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Controller_Plugin
{
  private $plugins       = array();
  private $pluginMethods = array();
  private $controller    = null;
  
  private $eventMethods = array("onBeforeAction", "onAfterAction", "onRedirect",
                                "onException", "onCreateController", "onExecuteAction");
  
  private $events = array();
  
  private static $instance = null;
  
  public static function create($controller = null)
  {
    if (self::$instance === null) self::$instance = new self();
    if ($controller !== null) self::$instance->setController($controller);
    
    return self::$instance;
  }
  
  public function setController($controller)
  {
    $this->controller = $controller;
  }
  
  public function add($plugin)
  {
    $name = get_class($plugin);
    
    if ($name === false) throw new Sabel_Exception_InvalidPlugin("can't locate");
    
    $this->plugins[$name] = $plugin;
    foreach (get_class_methods($plugin) as $method) {
      if (!in_array($method, $this->eventMethods)) {
        $this->pluginMethods[$method] = $name;
      } else {
        $this->events[$method][] = $name;
      }
    }
    
    return $this;
  }
  
  public function call($method, $arguments)
  {
    $obj = $this->plugins[$this->pluginMethods[$method]];
    $ref = new ReflectionClass($obj);
    return $ref->getMethod($method)->invokeArgs($obj, $arguments);
  }
  
  public function onBeforeAction()
  {
    foreach ($this->events["onBeforeAction"] as $name) {
      $this->plugins[$name]->onBeforeAction($this->controller);
    }    
  }
  
  public function onAfterAction()
  {
    foreach ($this->events["onAfterAction"] as $name) {
      $this->plugins[$name]->onAfterAction($this->controller);
    }
  }
  
  public function onRedirect($redirect)
  {
    foreach ($this->events["onRedirect"] as $name) {
      $this->plugins[$name]->onRedirect($this->controller, $redirect);
    }
  }
  
  public function onException($exception)
  {
    foreach ($this->events["onException"] as $name) {
      $this->plugins[$name]->onException($this->controller, $exception);
    }
  }
  
  public function onCreateController($controller, $candidate)
  {
    foreach ($this->events["onCreateController"] as $name) {
      $this->plugins[$name]->onCreateController($controller, $candidate);
    }
  }
  
  public function onExecuteAction($method)
  {
    $result = true;
    
    if (isset($this->events["onExecuteAction"])) {
      foreach ($this->events["onExecuteAction"] as $name) {
        $result = $this->plugins[$name]->onExecuteAction($method);
      }
    }
    
    return $result;
  }
  
  public function toArray()
  {
    return $this->plugins;
  }
}