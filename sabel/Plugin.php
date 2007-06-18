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
final class Sabel_Plugin
{
  private $plugins       = array();
  private $pluginMethods = array();
  
  private $controller    = null;
  private $destination   = null;
  private $executer      = null;
  
  const CREATE_CONTROLLER = "onCreateController";
  const BEFORE_ACTION     = "onBeforeAction";
  const AFTER_ACTION      = "onAfterAction";
  const REDIRECT          = "onRedirect";
  const EXCEPTION         = "onException";
  const EXECUTE_ACTION    = "onExecuteAction";
  
  private $eventMethods = array(self::CREATE_CONTROLLER,
                                self::BEFORE_ACTION,
                                self::AFTER_ACTION,
                                self::REDIRECT,
                                self::EXCEPTION,
                                self::EXECUTE_ACTION);
  
  private $events = array();
  
  private static $instance = null;
  
  public static function create($controller = null)
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    if ($controller !== null) {
      self::$instance->setController($controller);
    }
    
    return self::$instance;
  }
  
  public function setController($controller)
  {
    $this->controller = $controller;
  }
  
  public function setExecuter($executer)
  {
    $this->executer = $executer;
  }
  
  public function setDestination($destination)
  {
    $this->destination = $destination;
  }
  
  /**
   * add plugin
   *
   * @param Sabel_Plugin_Base $plugin
   */
  public function add($plugin)
  {
    if (!$plugin instanceof Sabel_Plugin_Base) {
      throw new Sabel_Exception_Unexpected();
    }
    
    $pluginName = get_class($plugin);
    $this->plugins[$pluginName] = $plugin;
    
    $ref = new ReflectionClass($pluginName);
    
    $notPluginMethods = array("setController",
                              "setDestination",
                              "__construct",
                              "__destruct");
    
    foreach ($ref->getMethods() as $method) {
      $name = $method->getName();
      
      if ($method->isPublic()) {
        if (!in_array($name, $notPluginMethods)) {
          $this->registration($name, $pluginName);
        }
      }
    }
    
    return $this;
  }
  
  private final function registration($method, $pluginName)
  {
    if ($this->isEventMethod($method)) {
      Sabel_Context::log("[Plugin] " . $pluginName . " event " . $method);
      $this->events[$method][] = $pluginName;
    } else {
      Sabel_Context::log("[Plugin] " . $pluginName . " call " . $method);
      $this->pluginMethods[$method] = $pluginName;
    }
  }
  
  /**
   * execute a method of plugin
   *
   * @param string $method
   * @param array $arguments
   * @return mixed the result of action execute
   */
  public function call($method, $arguments)
  {
    if (!$this->isPluginMethodExists($method)) {
      $msg = "call {$method}() not found in any plugins and controller";
      throw new Sabel_Exception_NoPluginMethod($msg);
    }
    
    $plugin = $this->plugins[$this->pluginMethods[$method]];
    if (!is_object($plugin)) throw new Sabel_Exception_Unexpected();
    
    $plugin->setController($this->controller);
    $refPlugin = new ReflectionClass($plugin);
    
    return $refPlugin->getMethod($method)->invokeArgs($plugin, $arguments);
  }
  
  /**
   * before execute action event
   *
   * @return boolean
   */
  public function onBeforeAction()
  {
    Sabel_Context::log("[Plugin] execute on before");
    return $this->doActionEvent("onBeforeAction");
  }
  
  public function onAfterAction()
  {
    Sabel_Context::log("[Plugin] execute on after");
    $this->doActionEvent("onAfterAction");
  }
  
  public function onRedirect($redirect)
  {
    $event = self::REDIRECT;
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->getPlugin($name);
        $plugin->$event($redirect);
      }
    }
  }
  
  public function onException($exception)
  {
    $event = self::EXCEPTION;
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->getPlugin($name);
        $plugin->$event($exception);
      }
    }
  }
  
  public function onCreateController($destination)
  {
    $event = self::CREATE_CONTROLLER;
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->getPlugin($name);
        $plugin->$event($destination);
      }
    }
  }
  
  public function hasExecuteAction()
  {
    $event = self::EXECUTE_ACTION;
    return (isset($this->events[$event]));
  }
  
  public function onExecuteAction($method)
  {
    $result = false; 
    $event = self::EXECUTE_ACTION;
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->getPlugin($name);
        $result = $plugin->$event($method);
      }
    }
    
    return $result;
  }
  
  public function toArray()
  {
    return $this->plugins;
  }
  
  private final function getPlugin($name)
  {
    $plugin = $this->plugins[$name];
    $plugin->setController($this->controller);
    $plugin->setDestination($this->destination);
    return $plugin;
  }
  
  private final function doActionEvent($event)
  {
    $proceed = true;
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->plugins[$name];
        $plugin->setController($this->controller);
        $plugin->setDestination($this->destination);
        $proceed = $plugin->$event();
        if ($proceed === null) $proceed = true;
      }
    }
    
    return $proceed;
  }
  
  private final function isPluginMethodExists($method)
  {
    return isset($this->pluginMethods[$method]);
  }
  
  private final function isEventMethod($method)
  {
    return in_array($method, $this->eventMethods);
  }
}
