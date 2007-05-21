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
  const REDIRECTED = "SABEL_CONTROLLER_REDIRECTED";
  
  public
    $plugin = null;
  
  protected
    $redirect    = "",
    $redirected  = false,
    $rendered    = "",
    $result      = null;
    
  protected
    $attributes  = array(),
    $assignments = array();
  
  protected
    $request  = null,
    $storage  = null;
    
  protected
    $enableStorage = true;
  
  /**
   * reserved name lists of methods(actions)
   * @var array $reserved
   */
  private $reserved = array("setup",
                            "getAction",
                            "getRequests",
                            "execute",
                            "getResult",
                            "result",
                            "initialize");
  
  /**
   * default constructer of page controller
   *
   */
  public function __construct()
  {
    $this->plugin = Sabel_Controller_Plugin::create($this);
  }
  
  /**
   * initialize a controller.
   * execute ones before action execute.
   *
   */
  public function initialize(){}
  
  /**
   * setup of PageController
   *
   * @access public
   * @param Sabel_Request $request
   * @param Sabel_Storage $storage
   * @return void
   */
  public function setup(Sabel_Request $request, $storage = null)
  {
    $this->request = $request;
    
    if ($this->enableStorage) {
      if ($storage === null) {
        $this->storage = Sabel_Storage_Session::create();
      } else {
        $this->storage = $storage;
      }
      
      Sabel_Context::setStorage($this->storage);
    }
  }
  
  /**
   * execute action
   *
   * @access public
   * @param string $action action method name
   * @return mixed result of execute an action.
   */
  public function execute($action)
  {
    try {
      if (empty($action)) {
        throw new Sabel_Exception_InvalidActionName("invalid action name");
      }
      
      if (isset($this->reserved[$this->action])) {
        throw new Sabel_Exception_Runtime("use reserved action name");
      }
      
      $proceed = $this->plugin->onBeforeAction();
      
      if ($proceed) {
        $this->result = $result = $this->$action();
      }
      
    } catch (Exception $exception) {
      $this->plugin->onException($exception);
    }
    
    $this->plugin->onAfterAction();
    
    return $result;
  }
  
  /**
   * HTTP Redirect to another location.
   *
   * @access public
   * @param string $to /Module/Controller/Method
   * @return mixed self::REDIRECTED
   */
  public function redirect($to)
  {
    $this->redirect   = $to;
    $this->redirected = true;
    
    $this->plugin->onRedirect($to);
    $this->plugin->onAfterAction();
    
    return self::REDIRECTED;
  }
  
  /**
   * HTTP Redirect to another location with uri.
   *
   * @param string $params
   */
  public function redirectTo($params)
  {
    if (!is_array($params) && is_string($params)) {
      $params = array(':action' => $params);
    }
    
    $candidate = Sabel_Context::getCandidate();
    return $this->redirect($candidate->uri($params));
  }
  
  public function isRedirected()
  {
    return $this->redirected;
  }
  
  public function getRedirect()
  {
    return $this->redirect;
  }
  
  /**
   * assign value to template.
   *
   * @param mixed $key search key
   * @param mixed $value value
   */
  protected function assign($key, $value)
  {
    $this->assignments[$key] = $value;
  }
  
  public function getAttributes()
  {
    return $this->attributes;
  }
  
  public function setAttribute($name, $value)
  {
    $this->attributes[$name] = $value;
  }
  
  public function setAttributes($attributes)
  {
    $this->attributes = array_merge($attributes, $this->attributes);
  }
  
  public function getResult()
  {
    return $this->result;
  }
  
  public function getRequest()
  {
    return $this->request;
  }
  
  public function setAction($action)
  {
    $this->action = $action;
  }
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function getStorage()
  {
    return $this->storage;
  }
  
  public function getRequests()
  {
    return $this->request->getPostRequests();
  }
  
  public function getAssignments()
  {
    return $this->assignments;
  }
  
  public function hasRendered()
  {
    return (strlen($this->rendered) !== 0);
  }
  
  public function getRendered()
  {
    return $this->rendered;
  }
  
  protected function __get($name)
  {
    if (array_key_exists($name, $this->attributes)) {
      $result = $this->attributes[$name];
    } else {
      $result = $this->request->getParameter($name);
      $result = ($result === false || $result === "") ? null : $result;
    }
    
    return $result;
  }
  
  protected function __set($name, $value)
  {
    $this->attributes[$name] = $value;
  }
  
  protected function __call($method, $arguments)
  {
    return $this->plugin->call($method, $arguments);
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

class Sabel_Exception_ReservedActionName extends Sabel_Exception_Runtime {}
class Sabel_Exception_InvalidActionName  extends Sabel_Exception_Runtime {}
class Sabel_Exception_InvalidPlugin      extends Sabel_Exception_Runtime {}
