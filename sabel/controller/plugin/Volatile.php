<?php

/**
 * Volatile plugin
 *
 * @category   Controller
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_Volatile implements Sabel_Controller_Page_Plugin
{
  private $volatiles = array();
  private $lists     = array();
  private $ignores   = array();
  private $storage   = null;
  
  private $module = "";
  private $controller = "";
  private $action = "";
  
  public function __construct($storage = null, $candidate = null)
  {
    if ($storage === null) {
      $this->storage = Sabel_Storage_Session::create();
    } else {
      $this->storage = $storage;
    }
    
    if ($candidate === null) {
      $candidate = Sabel_Context::getCurrentCandidate();
      if (!is_object($candidate)) {
        throw new Sabel_Exception_Runtime("candidate is not object.");
      }
    } else {
      $candidate = $candidate;
    }
    
    $this->module     = $candidate->getModule();
    $this->controller = $candidate->getController();
    $this->action     = $candidate->getAction();
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
  
  public function volatile($key, $value, $options = null)
  {
    if (is_array($options)) {
      if (isset($options["on"])) {
        if (isset($options["on"]["module"])) {
          $module = $options["on"]["module"];
        } else {
          $module = $this->module;
        }
        
        if (isset($options["on"]["controller"])) {
          $controller = $options["on"]["controller"];
        } else {
          $controller = $this->controller;
        }
        
        if (isset($options["on"]["action"])) {
          $action = $options["on"]["action"];
        } else {
          $action = $this->action;
        }
        
        $this->lists[$key] = array("module" => $module, "controller" => $controller, "action" => $action);
      }
    }
    
    if (is_array($options)) {
      if (isset($options["ignores"])) {
        foreach ($options["ignores"] as $ignore) {
          if (isset($ignore["module"])) {
            $module = $ignore["ignore"]["module"];
          } else {
            $module = $this->module;
          }
          
          if (isset($ignore["controller"])) {
            $controller = $ignore["controller"];
          } else {
            $controller = $this->controller;
          }
          
          if (isset($ignore["action"])) {
            $action = $ignore["action"];
          } else {
            $action = $this->action;
          }
          $this->ignores[$key][] = array("module" => $module, "controller" => $controller, "action" => $action);
        }
      }
    }
    
    $this->volatiles[$key] = $value;
  }
  
  public function getVolatiles()
  {
    return $this->volatiles;
  }
  
  public function onBeforeAction($controller)
  {
    $this->volatiles = $this->storage->read("volatiles");
    $this->lists     = $this->storage->read("volatiles_lists");
    $this->ignores   = $this->storage->read("volatiles_ignores");
    
    if (is_array($this->volatiles)) {
      $attributes = array_merge($this->volatiles, $controller->getAttributes());
      $controller->setAttributes($attributes);
      
      foreach ($this->storage->read("volatiles") as $key => $vvalue) {
        if (isset($this->ignores[$key])) {
          $hitIgnoresList = false;
          foreach ($this->ignores[$key] as $ignore) {
            if ($this->module     === $ignore["module"]     &&
                $this->controller === $ignore["controller"] &&
                $this->action     === $ignore["action"])
            {
              $hitIgnoresList = true;
            }
          }
          
          if (!$hitIgnoresList) {
            unset($this->ignores[$key]);
            unset($this->volatiles[$key]);
            $this->storage->delete($key);
          }
        } else {
          unset($this->volatiles[$key]);
          $this->storage->delete($key);
        }
      }
    }
  }
  
  public function onAfterAction($controller)
  {
    $this->shutdown($controller);
  }
  
  public function onRedirect($controller)
  {
    $this->shutdown($controller);
  }
  
  protected function shutdown($controller)
  {
    if (is_array($this->storage->read("volatiles"))) {
      foreach ($this->storage->read("volatiles") as $key => $vvalue) {
        if (isset($this->lists[$key])) {
          if ($this->module     === $this->lists[$key]["module"]     &&
              $this->controller === $this->lists[$key]["controller"] &&
              $this->action     === $this->lists[$key]["action"])
          {
            $this->storage->delete($key);
            unset($this->volatiles[$key]);
          }
        }
      }
    }
    
    $this->storage->write("volatiles", $this->volatiles);
    $this->storage->write("volatiles_lists", $this->lists);
    $this->storage->write("volatiles_ignores", $this->ignores);
  }
  
  public function onException($controller, $exception) {}
}
