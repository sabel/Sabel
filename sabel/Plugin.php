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
  const ENABLE_METHOD = "enable";
  
  private $plugins       = array();
  private $pluginMethods = array();
  private $controller    = null;
  private $executer      = null;
  
  private $eventMethods = array("onCreateController",
                                "onBeforeAction",
                                "onAfterAction",
                                "onRedirect",
                                "onException",
                                "onExecuteAction");
  
  private $events = array();
  
  private static $instance = null;
  
  public static function create($controller = null)
  {
    if (self::$instance === null) self::$instance = new self();
    self::$instance->setController($controller);
    
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
    
    if (method_exists($plugin, self::ENABLE_METHOD)) {
      foreach ($plugin->enable() as $method) {
        if (ENVIRONMENT === DEVELOPMENT) {
          Sabel_Context::log("enable plugin: " . $pluginName . " on " . $method);
        }
        if ($this->isEventMethod($method)) {
          $this->events[$method][] = $pluginName;
        } else {
          $this->pluginMethods[$method] = $pluginName;
        }
      }
    }
    
    return $this;
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
    return $this->doActionEvent("onBeforeAction");
  }
  
  public function onAfterAction()
  {
    $this->doActionEvent("onAfterAction");
  }
  
  public function onRedirect($redirect)
  {
    $event = "onRedirect";
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $this->plugins[$name]->$event($redirect);
      }
    }
  }
  
  public function onException($exception)
  {
    $event = "onException";
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $this->plugins[$name]->$event($exception);
      }
    }
  }
  
  public function onCreateController($destination)
  {
    $event = "onCreateController";
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->plugins[$name];
        $plugin->setController($this->controller);
        $plugin->setDestination($destination);
        $plugin->$event($destination);
      }
    }
  }
  
  public function hasExecuteAction()
  {
    $event = "onExecuteAction";
    return (isset($this->events[$event]));
  }
  
  public function onExecuteAction($method)
  {
    $result = false; 
    $event = "onExecuteAction";
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->plugins[$name];
        $plugin->setController($this->controller);
        $plugin->setDestination($this->destination);
        $result = $plugin->$event($method);
      }
    }
    
    return $result;
  }
  
  public function toArray()
  {
    return $this->plugins;
  }
  
  private final function doActionEvent($event)
  {
    $proceed = true;
    
    if (isset($this->events[$event])) {
      foreach ($this->events[$event] as $name) {
        $plugin = $this->plugins[$name];
        $plugin->setController($this->controller);
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
