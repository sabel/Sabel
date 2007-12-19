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
    $request  = null,
    $response = null;
  
  protected
    $executed = false;
    
  protected
    $bus      = null,
    $storage  = null;
    
  protected
    $setup       = false,
    $hidden      = array(),
    $reserved    = array(),
    $attributes  = array(),
    $assignments = array(),
    $destination = null;
  
  /**
   * default constructer of page controller
   *
   */
  public final function __construct()
  {
    $this->reserved = get_class_methods("Sabel_Controller_Page");
    
    $injector = Sabel_Container::create(new Config_Factory());
    $this->response = $injector->newInstance("Sabel_Response");
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
   * @access public
   * @param Sabel_Request $request
   * @param Sabel_Storage $storage
   * @return void
   */
  public function setup($request, $destination, $storage = null)
  {
    if (!$request instanceof Sabel_Request_Object) {
      throw new Sabel_Exception_InvalidArgument("invalid request object");
    }
    
    if (!$destination instanceof Sabel_Destination) {
      throw new Sabel_Exception_InvalidArgument("invalid destination object");
    }
    
    $this->request     = $request;
    $this->destination = $destination;
    $this->storage     = $storage;
    $this->setup = true;
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
  public function execute($action, $params = array())
  {
    if (!$this->setup) {
      throw new Sabel_Exception_Runtime("page controller must be setup");
    }
    
    if (!$this->request instanceof Sabel_Request_Object) {
      throw new Sabel_Exception_Runtime("invalid request object");
    }
    
    if ($this->request->isTypeOf("css")) {
      $this->response->setContentType("text/css");
    }
    
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
      }
      
      $this->response->success();
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
  
  /**
   * assign value to template.
   *
   * @param mixed $key search key
   * @param mixed $value value
   */
  protected function assign($key, $value)
  {
    $this->attributes[$key] = $value;
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
  
  public final function getRequests()
  {
    return $this->request->fetchPostValues();
  }
  
  public function getResponse()
  {
    return $this->response;
  }
  
  public final function getAssignments()
  {
    return $this->assignments;
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
  
  /**
   * get parameter value of URI Query String
   *
   * @param string parameters key
   * @return mixed
   */
  protected function getParameter($key)
  {
    return $this->request->getParameters()->get($key);
  }
  
  protected function isPost()
  {
    return ($this->request->isPost());
  }
  
  protected function isGet()
  {
    return ($this->request->isGet());
  }
}
