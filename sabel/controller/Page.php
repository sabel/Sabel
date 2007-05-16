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
abstract class Sabel_Controller_Page extends Sabel_Controller_Page_Base
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
    $enableSession = true;
  
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
   * @param Sabel_Request $request
   * @param Sabel_Storage $storage
   */
  public function setup(Sabel_Request $request, $storage = null)
  {
    $this->request = $request;
    
    if ($this->enableSession) {
      if ($storage === null) {
        $this->storage = Sabel_Storage_Session::create();
      } else {
        $this->storage = $storage;
      }
      
      Sabel_Context::setStorage($this->storage);
    }
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
  
  public function getRequests()
  {
    return $this->request->getPostRequests();
  }
  
  public function execute($action)
  {
    try {
      if (empty($action)) {
        throw new Sabel_Exception_InvalidActionName("invalid action name");
      }
      
      if (isset($this->reserved[$this->action])) {
        throw new Sabel_Exception_Runtime('use reserved action name');
      }
      
      $this->plugin->onBeforeAction();
      
      $this->result = $result = $this->$action();
    } catch (Exception $exception) {
      $this->plugin->onException($exception);
    }
    
    $this->plugin->onAfterAction();
    
    return $this;
  }
  
  /*
  protected function processAction($action)
  {
    $actionResult =(array) $this->$action();
    
    $result = (is_array($actionResult)) ? $actionResult : array();
    $this->result = $result;
    
    return $result;
  }
  
  protected function processAction($action)
  {
    if ($this->redirected) return false;
    
    $methodAction = "";
    
    if (is_object($this->request)) {
      $reqMethod    = strtolower($this->request->getHttpMethod());
      $methodAction = $reqMethod . ucfirst($action);
    }
    $actionResult = array();
    
    
    if (in_array($methodAction, $this->reserved)) {
      throw new Sabel_Exception_ReservedActionName($methodAction . " is reserved by sabel");
    }
    
    if ($this->hasMethod($methodAction)) {
      if (($actionResult = $this->plugin->onExecuteAction($action)) === false) {
        $actionResult =(array) $this->$methodAction();
      }
    } elseif ($this->hasMethod($action)) {
      if (($actionResult = $this->plugin->onExecuteAction($action)) === false) {
        $actionResult = $this->$action();
      }
    } elseif ($this->hasMethod('actionMissing')) {
      $this->actionMissing();
    }
    
    $result = (is_array($actionResult)) ? $actionResult : array();
    $this->result = $result;
    
    return $result;
  }
  */
  
  /**
   * HTTP Redirect to another location.
   *
   * @param string $to /Module/Controller/Method
   */
  public function redirect($to)
  {
    $this->redirected = true;
    $this->redirect = $to;
    $this->plugin->onRedirect($to);
    $this->plugin->onAfterAction();
    
    return self::REDIRECTED;
  }
  
  public function redirectTo($params)
  {
    if (!is_array($params) && is_string($params)) {
      $params = array(':action' => $params);
    }
    
    $candidate = Sabel_Context::getCurrentCandidate();
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
