<?php

/**
 * Abstract Page Controller
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Controller_Page extends Sabel_Object
{
  public
    $request  = null;
    
  protected
    $executed = false;
    
  protected
    $bus      = null,
    $response = null,
    $storage  = null;
    
  protected
    $setup       = false,
    $hidden      = array(),
    $reserved    = array(),
    $attributes  = array();
  
  /**
   * default constructer of page controller
   *
   */
  public final function __construct(Sabel_Response $response)
  {
    $this->reserved = get_class_methods("Sabel_Controller_Page");
    $this->response = $response;
  }
  
  /**
   * initialize a controller.
   * execute ones before action execute.
   *
   */
  public function initialize()
  {
    
  }
  
  /**
   * setup of PageController
   *
   * @param Sabel_Request $request
   * @param Sabel_Storage $storage
   *
   * @return void
   */
  public function setup(Sabel_Request $request, $storage = null)
  {
    $this->request = $request;
    $this->storage = $storage;
    $this->setup   = true;
  }
  
  public function setBus($bus)
  {
    $this->bus = $bus;
  }
  
  /**
   * execute action
   *
   * @access public
   * @param string $action action method name
   * @return mixed result of execute an action.
   */
  public function execute($action = null, $params = array())
  {
    if (!$this->setup) {
      throw new Sabel_Exception_Runtime("page controller must be setup");
    }
    
    if ($this->request->isTypeOf("css")) {
      $this->response->setContentType("text/css");
    }
    
    if ($action === null) $action = $this->action;
    
    if ($this->isReserved($action)) {
      $this->response->notfound();
    } elseif ($this->isHiddenAction($action)) {
      $this->response->notfound();
    } else {
      if ($this->isActionExists($action)) {
        if (count($params) >= 1) {
          call_user_func_array(array($this, $action), $params);
        } else {
          $this->$action();
        }
        
        $this->executed = true;
        if (!$this->response->isFailure()) {
          $this->response->success();
        }
      } else {
        $this->response->success();
      }
    }
    
    return $this;
  }
  
  public function isExecuted()
  {
    return $this->executed;
  }
  
  private function isReserved($action)
  {
    return in_array($action, $this->reserved);
  }
  
  private function isHiddenAction($action)
  {
    return in_array($action, $this->hidden);
  }
  
  private function isActionExists($action)
  {
    return ($this->hasMethod($action) && is_callable(array($this, $action)));
  }
  
  public function getAttribute($name)
  {
    if (isset($this->attributes[$name])) {
      return $this->attributes[$name];
    } else {
      return null;
    }
  }
  
  public function setAttribute($name, $value)
  {
    $this->attributes[$name] = $value;
  }
  
  public function getAttributes()
  {
    return $this->attributes;
  }
  
  public function setAttributes($attributes)
  {
    $this->attributes = array_merge($this->attributes, $attributes);
  }
  
  public function hasAttribute($name)
  {
    return array_key_exists($name, $this->attributes);
  }
  
  public function isAttributeSet($name)
  {
    return isset($this->attributes[$name]);
  }
  
  public final function setAction($action)
  {
    $this->action = $action;
  }
  
  public final function getAction()
  {
    return $this->action;
  }
  
  public final function getStorage()
  {
    return $this->storage;
  }
  
  public function getResponse()
  {
    return $this->response;
  }
  
  protected function __get($name)
  {
    if (array_key_exists($name, $this->attributes)) {
      return $this->attributes[$name];
    } else {
      return null;
    }
  }
  
  protected function __set($name, $value)
  {
    $this->attributes[$name] = $value;
  }
}
