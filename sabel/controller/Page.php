<?php

Sabel::using("Sabel_Controller_Page_Base");
Sabel::using("Sabel_Controller_Page_Plugin");
Sabel::using("Sabel_Logger_Factory");
Sabel::using('Sabel_Exception_Runtime');

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
  const HTTP_METHOD_GET    = 0x01;
  const HTTP_METHOD_POST   = 0x05;
  const HTTP_METHOD_PUT    = 0x10;
  const HTTP_METHOD_DELETE = 0x15;
  
  protected $result = null;
  
  protected
    $view       = null,
    $request    = null,
    $httpMethod = self::HTTP_METHOD_GET,
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
    
  protected
    $plugins       = array(),
    $pluginMethods = array();
    
  protected $models = null;
  
  /**
   * reserved name lists of methods(actions)
   * @var array $reserved
   */
  private $reserved = array('setup',
                            'getAction',
                            'getRequests',
                            'execute',
                            'initialize');
                            
  public function initialize() {}
  
  /**
   * setup of PageController
   *
   * @todo remove depend to view
   */
  public function setup(Sabel_Request $request, $view = null, $action = null)
  {
    $this->action  = $action;
    $this->request = $request;
    $this->logger  = Sabel_Logger_Factory::create("file");
    
    $this->view = ($view === null) ? Sabel::load('Sabel_View') : $view;
    Sabel_Context::setView($this->view);
    
    if ($this->enableSession) {
      Sabel::using('Sabel_Storage_Session');
      $this->storage = Sabel_Storage_Session::create();
    }
    
    if (isset($_SERVER['REQUEST_METHOD'])) {
      $this->httpMethod = $_SERVER['REQUEST_METHOD'];
    }
    
    $this->registPlugin(Sabel::load('Sabel_Controller_Plugin_Volatile'));
    $this->registPlugin(Sabel::load('Sabel_Controller_Plugin_Filter'));
    $this->registPlugin(Sabel::load('Sabel_Controller_Plugin_View'));
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
    $obj = $this->plugins[$this->pluginMethods[$method]];
    $ref = new ReflectionClass($obj);
    return $ref->getMethod($method)->invokeArgs($obj, $arguments);
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
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function getRequests()
  {
    return $this->request->getPostRequests();
  }
  
  public function execute($action = null)
  {
    if ($action !== null) $this->action = $action;
    
    if (empty($action)) {
      throw new Sabel_Exception_InvalidActionName("invalid action name");
    }
    
    if (isset($this->reserved[$this->action])) {
      throw new Sabel_Exception_Runtime('use reserved action name');
    }
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $this->withLayout = false;
      if ($this->view->isTemplateMissing()) {
        $this->rendering = false;
      }
    }
    
    $this->processBeforeActionPlugins();
    $this->processAction();
    $this->processAfterActionPlugins();
    
    return $this->result;
  }
  
  public function registPlugin($plug)
  {
    $name = get_class($plug);
    $this->plugins[$name] = $plug;
    foreach (get_class_methods($plug) as $method) {
      if ($method !== 'onBeforeAction' && $method !== 'onAfterAction') {
        $this->pluginMethods[$method] = $name;
      }
    }
  }
  
  protected function processBeforeActionPlugins()
  {
    foreach ($this->plugins as $plugin) $plugin->onBeforeAction($this);
  }
  
  protected function processAfterActionPlugins()
  {
    foreach ($this->plugins as $plugin) $plugin->onAfterAction($this);
  }
  
  protected function processRedirectPlugins()
  {
    foreach ($this->plugins as $plugin) $plugin->onRedirect($this);
  }
  
  protected function processAction()
  {
    $action       = $this->action;
    $reqMethod    = strtolower($this->httpMethod);
    $methodAction = $reqMethod . ucfirst($action);
    $actionResult = array();
    
    if ($this->hasMethod($methodAction)) {
      $this->logger->log("execute method action: $methodAction");
      $actionResult =(array) $this->$methodAction();
      if (!$this->skipDefaultAction) {
        $this->logger->log("execute action: $action");
        if ($this->hasMethod($action)) {
          $actionResult = array_merge((array) $this->$action(), $actionResult);
        }
      }
    } elseif ($this->hasMethod($action)) {
      $this->logger->log("execute action: $action");
      $actionResult = $this->$action();
    } elseif ($this->hasMethod('actionMissing')) {
      $this->actionMissing();
    }
    
    $this->result = (is_array($actionResult)) ? $actionResult : array();
  }
  
  public function rendering()
  {
    if (!$this->rendering) return;
    
    if ($this->view->isTemplateMissing()) {
      if ($this->hasMethod('templateMissing')) {
        $this->templateMissing();
      } else {
        throw Sabel::load('Sabel_Exception_TemplateMissing', var_export($this->view, 1));
      }
    } else {
      if ($this->hasMethod("render")) {
        $rendered = $this->view->rendering($this->withLayout);
        return $this->render($rendered);
      } else {
        return $this->view->rendering($this->withLayout);
      }
    }
  }
  
  protected function checkReferer($validURIs)
  {
    $host = $_SERVER['HTTP_HOST'];
    $ref  = $_SERVER['HTTP_REFERER'];
    $patternAbsoluteURI = '%http://' . $host . $validURIs[0]. '%';
    return (bool) preg_match($patternAbsoluteURI, $ref);
  }
  
  protected function layout($layout)
  {
    $this->view->setLayout($layout);
  }
  
  /**
   * HTTP Redirect to another location.
   * this method will avoid "back button" problem.
   *
   * @param string $to /Module/Controller/Method
   */
  public function redirect($to)
  {
    if (isset($_SERVER['HTTP_HOST'])) {
      $host = $_SERVER['HTTP_HOST'];
    } else {
      $host = "localhost";
    }
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . $to;
    $this->processRedirectPlugins();
    header ($redirect);
    exit;
  }
  
  public function redirectTo($params)
  {
    if (!is_array($params) && is_string($params)) {
      $params = array(':action' => $params);
    }
    
    $candidate = Sabel_Context::getCurrentCandidate();
    $this->redirect('/' . $candidate->uri($params));
  }
  
  /**
   * forwaring anothor controller or method of same controller.
   *
   */
  protected function forward($to)
  {
    // @todo implemen
  }
  
  /**
   * assign value to template.
   *
   * @param mixed $key search key
   * @param mixed $value value
   */
  protected function assign($key, $value)
  {
    $this->logger->log("assign to view: $key");
    $this->view->assign($key, $value);
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
    return ($this->httpMethod === self::HTTP_METHOD_POST);
  }
  
  protected function isGet()
  {
    return ($this->httpMethod === self::HTTP_METHOD_GET);
  }
}

class Sabel_Exception_InvalidActionName extends Sabel_Exception_Runtime{}
