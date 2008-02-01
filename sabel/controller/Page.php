<?php

/**
 * Abstract Page Controller
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Controller_Page extends Sabel_Object
{
  /**
   * @var Sabel_Request
   */
  protected $request = null;
  
  /**
   * @var Sabel_Response
   */
  protected $response = null;
  
  /**
   * @var Sabel_Controller_Redirector
   */
  protected $redirect = null;
  
  /**
   * @var Sabel_Storage_Abstract
   */
  protected $storage = null;
  
  /**
   * @var Sabel_Bus
   */
  protected $bus = null;
  
  /**
   * @var boolean
   */
  protected $executed = false;
  
  /**
   * @var boolean
   */
  protected $setup = false;
  
  /**
   * @var array
   */
  protected $hidden = array();
  
  /**
   * @var array
   */
  protected $reserved = array();
  
  /**
   * @var array
   */
  protected $attributes = array();
  
  /**
   * default constructer of page controller
   */
  public final function __construct(Sabel_Response $response)
  {
    $this->reserved = get_class_methods(__CLASS__);
    $this->response = $response;
  }
  
  /**
   * setup of PageController
   *
   * @param Sabel_Request               $request
   * @param Sabel_Controller_Redirector $redirector
   * @param Sabel_Storage_Abstract      $storage
   *
   * @return void
   */
  public function setup(Sabel_Request               $request,
                        Sabel_Controller_Redirector $redirector,
                        Sabel_Storage_Abstract      $storage = null)
  {
    $this->request  = $request;
    $this->redirect = $redirector;
    $this->storage  = $storage;
    $this->setup    = true;
  }
  
  /**
   * initialize a controller.
   * execute ones before action execute.
   */
  public function initialize()
  {
    
  }
  
  public function setBus(Sabel_Bus $bus)
  {
    $this->bus = $bus;
  }
  
  public function getResponse()
  {
    return $this->response;
  }
  
  public function getRequest()
  {
    return $this->request;
  }
  
  public function getStorage()
  {
    return $this->storage;
  }
  
  public function getRedirector()
  {
    return $this->redirect;
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
    
    if ($action === null) $action = $this->action;
    
    if ($this->isReserved($action)) {
      $this->response->notfound();
    } elseif ($this->isHiddenAction($action)) {
      $this->response->notfound();
    } else {
      if ($this->isValidAction($action)) {
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
  
  public function isRedirected()
  {
    return $this->redirect->isRedirected();
  }
  
  private function isReserved($action)
  {
    return in_array($action, $this->reserved, true);
  }
  
  private function isHiddenAction($action)
  {
    return in_array($action, $this->hidden, true);
  }
  
  private function isValidAction($action)
  {
    if (!$this->hasMethod($action)) return false;
    
    $method = new ReflectionMethod($this->getName(), $action);
    return $method->isPublic();
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
