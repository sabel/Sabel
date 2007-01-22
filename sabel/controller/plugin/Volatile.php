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
  protected $volatiles = array();
  protected $lists     = array();
  protected $ignores   = array();
  
  public function volatile($key, $value, $options = null)
  {
    $candidate = Sabel_Context::getCurrentCandidate();
    
    if (is_array($options)) {
      if (isset($options["on"])) {
        if (isset($options["on"]["module"])) {
          $module = $options["on"]["module"];
        } else {
          $module = $candidate->getModule();
        }
        
        if (isset($options["on"]["controller"])) {
          $controller = $options["on"]["controller"];
        } else {
          $controller = $candidate->getController();
        }
        
        if (isset($options["on"]["action"])) {
          $action = $options["on"]["action"];
        } else {
          $action = $candidate->getAction();
        }
        
        $this->lists[$key] = array("module" => $module, "controller" => $controller, "action" => $action);
      }
    }
    
    if (is_array($options)) {
      if (isset($options["ignore"])) {
        if (isset($options["ignore"]["module"])) {
          $module = $options["ignore"]["module"];
        } else {
          $module = $candidate->getModule();
        }
        
        if (isset($options["ignore"]["controller"])) {
          $controller = $options["ignore"]["controller"];
        } else {
          $controller = $candidate->getController();
        }
        
        if (isset($options["ignore"]["action"])) {
          $action = $options["ignore"]["action"];
        } else {
          $action = $candidate->getAction();
        }
        
        $this->ignores[$key] = array("module" => $module, "controller" => $controller, "action" => $action);
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
    $storage = Sabel_Storage_Session::create();
    $this->volatiles = $storage->read("volatiles");
    $this->lists     = $storage->read("volatiles_lists");
    $this->ignores   = $storage->read("volatiles_ignores");
    
    if (is_array($this->volatiles)) {
      $attributes = array_merge($this->volatiles, $controller->getAttributes());
      $controller->setAttributes($attributes);
      
      foreach ($storage->read("volatiles") as $key => $vvalue) {
        if (isset($this->ignores[$key])) {
          $candidate = Sabel_Context::getCurrentCandidate();
          if (!($candidate->getModule()   === $this->ignores[$key]["module"]      &&
              $candidate->getController() === $this->ignores[$key]["controller"]  &&
              $candidate->getAction()     === $this->ignores[$key]["action"]))
          {
            unset($this->ignores[$key]);
            unset($this->volatiles[$key]);
            $storage->delete($key);
          }
        } else {
          unset($this->volatiles[$key]);
          $storage->delete($key);
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
    $storage = Sabel_Storage_Session::create();
    
    if (is_array($storage->read("volatiles"))) {
      foreach ($storage->read("volatiles") as $key => $vvalue) {
        if (isset($this->lists[$key])) {
          $candidate = Sabel_Context::getCurrentCandidate();
          if ($candidate->getModule()     === $this->lists[$key]["module"]     &&
              $candidate->getController() === $this->lists[$key]["controller"] &&
              $candidate->getAction()     === $this->lists[$key]["action"])
          {
            $storage->delete($key);
            unset($this->volatiles[$key]);
          }
        }
      }
    }
    
    $storage->write("volatiles", $this->volatiles);
    $storage->write("volatiles_lists", $this->lists);
    $storage->write("volatiles_ignores", $this->ignores);
  }
  
  public function onException($controller, $exception) {}
}