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
  protected
    $redirect    = "",
    $redirected  = false,
    $result      = null,
    $assignments = array();
  
  protected
    // $view       = null,
    $request    = null,
    $storage    = null,
    $logger     = null,
    $response   = null;
    
  protected
    $action            = '',
    $rendering         = true,
    $withLayout        = true,
    $attributes        = array(),
    $enableSession     = true,
    $skipDefaultAction = true;
  
  public $plugin = null;
    
  protected $models = null;
  
  /**
   * reserved name lists of methods(actions)
   * @var array $reserved
   */
  private $reserved = array('setup',
                            'getAction',
                            'getRequests',
                            'execute',
                            'getResult',
                            'result',
                            'initialize');
                            
  public function __construct()
  {
    $this->logger = load("Sabel_Logger_File", array("singleton" => true));
    Sabel_Context::log("construct " . get_class($this));
    $this->plugin = Sabel_Controller_Plugin::create($this);
  }
  
  public function initialize() {}
  
  /**
   * setup of PageController
   *
   * @todo remove depend to view
   */
  public function setup(Sabel_Request $request, $view = null, $storage = null)
  {
    Sabel_Context::log("setup controller " . get_class($this));
    
    $this->request = $request;
    // $this->view = ($view === null) ? new Sabel_View() : $view;
    
    if ($this->enableSession) {
      if ($storage === null) {
        $this->storage = Sabel_Storage_Session::create();
      } else {
        $this->storage = $storage;
      }
      
      Sabel_Context::setStorage($this->storage);
    }
  }
  
  protected function __get($name)
  {
    if (isset($this->attributes[$name])) {
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
    Sabel_Context::log("call plugin " . $method);
    return $this->plugin->call($method, $arguments);
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
  
  public function issetModels()
  {
    return ($this->models === null) ? false : true;
  }
  
  public function getModels()
  {
    return $this->models;
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
    Sabel_Context::log("execute {$this->action}");
    
    if (empty($action)) {
      throw new Sabel_Exception_InvalidActionName("invalid action name");
    }
    
    if (isset($this->reserved[$this->action])) {
      throw new Sabel_Exception_Runtime('use reserved action name');
    }
    
    $this->plugin->onBeforeAction();
    
    try {
      $result = $this->processAction($action);
      // $this->view->assignByArray($result);
    } catch (Exception $exception) {
      $this->plugin->onException($exception);
    }
    
    $this->plugin->onAfterAction();
    
    return $this;
  }
  
  public function partial($action)
  {
    if ($action !== null) {
      $result = $this->$action();
      
      $view = new Sabel_View();
      $view->assignByArray($result);
      $view->assignByArray($this->assignments);
      $condition = new Sabel_View_Locator_Condition(false);
      $condition->setCandidate(Sabel_Context::getCurrentCandidate());
      $condition->setName($action);
      $locator  = new Sabel_View_Locator_File();
      $resource = $locator->locate($condition);
      
      $result = $view->rendering($resource);
      return $result;
    }
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
      Sabel_Context::log("execute method action: $methodAction");
      if (($actionResult = $this->plugin->onExecuteAction($action)) === false) {
        $actionResult =(array) $this->$methodAction();
      }
      if (!$this->skipDefaultAction) {
        Sabel_Context::log("execute action: $action");
        if ($this->hasMethod($action)) {
          $actionResult = array_merge((array) $this->$action(), $actionResult);
        }
      }
    } elseif ($this->hasMethod($action)) {
      Sabel_Context::log("execute action: $action");
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
  
  /*
  public function rendering()
  {
    if (!$this->rendering) return;
        
    if ($this->view->isResourceMissing()) {
      if ($this->hasMethod('templateMissing')) {
        $this->resourceMissing();
      } else {
        throw Sabel::load('Sabel_Exception_TemplateMissing', var_export($this->view, 1));
      }
    } else {
      if ($this->hasMethod("render")) {
        $rendered = $this->view->rendering();
        return $this->render($rendered);
      } else {
        
        $content = $this->view->rendering();
        
        $view     = new Sabel_View();
        $resource = new Sabel_View_Resource_Template();
        $renderer = new Sabel_View_Renderer_Class();
        
        $view->assign("contentForLayout", $content);
        $view->setResource($resource);
        $resource->setRenderer($renderer);
        $resource->setPath(RUN_BASE . '/app/views/');
        $resource->setName('layout.tpl');
        
        return $view->rendering();
      }
    }
  }
  */
  
  public function disableLayout()
  {
    $this->withLayout = false;
    return $this;
  }
  
  protected function checkReferer($validURIs)
  {
    $host = Sabel_Environment::get("http_host");
    $ref  = Sabel_Environment::get("http_referer");
    
    $patternAbsoluteURI = '%http://' . $host . $validURIs[0]. '%';
    return (bool) preg_match($patternAbsoluteURI, $ref);
  }
  
  /*
  protected function layout($layout)
  {
    $this->view->setLayout($layout);
  }
  */
  
  /**
   * HTTP Redirect to another location.
   * this method will avoid "back button" problem.
   *
   * @param string $to /Module/Controller/Method
   */
  public function redirect($to)
  {
    $this->redirected = true;
    $this->redirect = $to;
    $this->plugin->onRedirect($to);
    $this->plugin->onAfterAction();
  }
  
  public function redirectTo($params)
  {
    if (!is_array($params) && is_string($params)) {
      $params = array(':action' => $params);
    }
    
    $candidate = Sabel_Context::getCurrentCandidate();
    $this->redirect($candidate->uri($params));
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
   * forwaring anothor controller or method of same controller.
   *
   */
  protected function forward($to)
  {
    // @todo implement this
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
    // $this->view->assign($key, $value);
  }
  
  public function getAssignments()
  {
    return $this->assignments;
  }
  
  protected function getType()
  {
    return $this->request->getUri()->getType();
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

class Sabel_Exception_ReservedActionName extends Sabel_Exception_Runtime{}
class Sabel_Exception_InvalidActionName extends Sabel_Exception_Runtime{}
class Sabel_Exception_InvalidPlugin extends Sabel_Exception_Runtime{}
