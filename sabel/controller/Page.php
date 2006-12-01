<?php

/**
 * the Base of Page Controller.
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Controller_Page
{
  protected
    $view        = null,
    $entry       = null,
    $cache       = null,
    $logger      = null,
    $request     = null,
    $httpMethod  = 'GET',
    $requests    = array(),
    $storage     = null,
    $response    = null,
    $template    = null,
    $container   = null,
    $destination = null;
    
  protected
    $security   = null,
    $identity   = null,
    $permission = Sabel_Security_Permission::P_PUBLIC;
    
  protected
    $action = '',
    $rendering = true,
    $enableSession = true,
    $skipDefaultAction = true;
    
  /**
   * reserved name lists of methods(actions)
   * @var array $reserved
   */
  private $reserved = array();
  
  public final function initializeReservedNamesOfMethods()
  {
    $ref = new ReflectionClass('Sabel_Controller_Page');
    $methods = $ref->getMethods();
    foreach ($methods as $method) $reserved[$method->getName()] = 1;
    $this->reserved = $reserved;
  }
  
  public function initialize()
  {
    // none.
  }
  
  public function setup()
  {
    $this->view = new Sabel_View();
    Sabel_Context::setView($this->view);
    $this->entry       = Sabel_Context::getCurrentMapEntry();
    
    $this->request     = $this->entry->getRequest();
    $this->requests    = $this->request->requests();
    $this->container   = Container::create();
    
    if ($this->enableSession) {
      $this->storage   = Sabel_Storage_Session::create();
      $this->security  = Sabel_Security_Security::create();
      $this->identity  = $this->security->getIdentity();
    }
    
    $this->destination = $this->entry->getDestination();
    $this->action      = $this->destination->action;
    
    if (isset($_SERVER['REQUEST_METHOD'])) {
      $this->httpMethod = $_SERVER['REQUEST_METHOD'];
    }
  }
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function getRequests()
  {
    return $this->request->requests();
  }
  
  public function execute()
  {
    if (!headers_sent()) header('X-Framework: Sabel');
    
    $actionName = $this->destination->getAction();
    
    // check reserved words
    if (isset($this->reserved[$actionName]))
      throw new Sabel_Exception_Runtime('use reserved action name');
      
    $result = null;
    if ($this->permission === Sabel_Security_Permission::P_PRIVATE ||
        $this->isPrivateAction($actionName)) {
      if ($this->isAuthorized()) {
        $result = $this->methodExecute($actionName);
      } elseif ($this->hasMethod('authorizeRequired')) {
        $this->authorizeRequired();
      } else {
        throw new Sabel_Exception_Runtime('must implement authorizeRequired() when P_PRIVATE');
      }
    } else {
      $result = $this->methodExecute($actionName);
    }
    
    if (is_array($result)) $this->view->assignByArray($result);
    return $result;
  }
  
  protected function methodExecute($action)
  {
    $reqMethod    = strtolower($this->httpMethod);
    $actionName   = $reqMethod . ucfirst($action);
    $actionResult = array();
    
    if ($this->hasMethod($actionName)) {
      $actionResult =(array) $this->test($actionName);
      if (!$this->skipDefaultAction) {
        $actionResult = array_merge((array) $this->test($action), $actionResult);
      }
    } elseif ($this->hasMethod($action)) {
      $actionResult = $this->test($action);
    } elseif ($this->hasMethod('actionMissing')) {
      $this->actionMissing();
    }
    $result = (is_array($actionResult)) ? $actionResult : array();
    
    if (is_object($this->storage))
      $this->storage->write('previous', $this->request->__toString());
      
    return $result;
  }
  
  protected function test($action)
  {
    $ref = new ReflectionClass($this);
    $method = $ref->getMethod($action);
    if ($method->getNumberOfParameters() === 0) {
      $actionResult = $this->$action();
    } else {
      $args = array();
      $parameters = $method->getParameters();
      foreach ($parameters as $parameter) {
        $name = $parameter->getName();
        $args[] = $this->$name;
      }
      $actionResult = $method->invokeArgs($this, $args);
    }
    return $actionResult;
  }
  
  public function rendering()
  {
    if (!$this->rendering) return;
    
    if ($this->view->isTemplateMissing()) {
      if ($this->hasMethod('templateMissing')) {
        $this->templateMissing();
      } else {
        throw new Sabel_Exception_TemplateMissing();
      }
    } else {
      return $this->view->rendering();
    }
  }
  
  protected function isPublicAction($actionName)
  {
    $ref = new ReflectionClass($this);
    $annot = $this->readAnnotation($ref->getName(), $actionName);
    if (isset($annot[0]) && is_object($annot[0])) {
      $annot = $annot[0];
      return ($annot->getContents() === 'public');
    }
    return false;
  }
  
  protected function isPrivateAction($actionName)
  {
    $ref = new ReflectionClass($this);
    $annot = $this->readAnnotation($ref->getName(), $actionName);
    if (isset($annot[0]) && is_object($annot[0])) {
      $annot = $annot[0];
      return ($annot->getContents() === 'private');
    }
    return false;
  }
  
  protected function __get($name)
  {
    if ($this->request->hasUriValue($name)) {
      return $this->request->$name;
    } else {
      $tmp = $this->getRequests();
      return (isset($tmp[$name])) ? $tmp[$name] : null;
    }
  }
  
  protected function __set($name, $value)
  {
    $this->assign($name, $value);
  }
  
  protected function __call($method, $args)
  {
    if ($this->request->hasMethod($method))
      return $this->request->$method($args);
  }
  
  public function hasMethod($name)
  {
    return (method_exists($this, $name));
  }
  
  public function successMethod()
  {
    return 'success' . ucfirst($this->getAction());
  }
  
  public function hasSuccessMethod()
  {
    return ($this->hasMethod($this->successMethod()));
  }
  
  public function errorMethod()
  {
    return 'error' . ucfirst($this->getAction());
  }
  
  public function hasErrorMethod()
  {
    return ($this->hasMethod($this->errorMethod()));
  }
  
  protected function checkReferer($validURIs)
  {
    $ref  = Sabel_Env_Server::create()->http_referer;
    $replaced = preg_replace('/\\//', '\/', $validURIs[0]);
    $patternAbsoluteURI = '/http:\/\/' . $host . $replaced . '/';
    preg_match($patternAbsoluteURI, $ref, $matchs);
    return (isset($matchs[0])) ? true : false;
  }
  
  protected function layout($layout)
  {
    $this->view->setLayout($layout);
  }
  
  protected function proxy($target)
  {
    return new Sabel_Aspect_Proxy($target);
  }
  
  /**
   * HTTP Redirect to another location.
   * this method will avoid "back button" problem.
   *
   * @param string $to /Module/Controller/Method
   */
  public function redirect($to)
  {
    $host = Sabel_Env_Server::create()->http_host;
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . $to;
    header($redirect);
    
    exit; // exit after HTTP Header(30x)
  }
  
  public function redirectTo($params)
  {
    if (!is_array($params) && is_string($params)) {
      $params = array('action'=>$params);
    }
    
    $entry = null;
    
    $map = Sabel_Map_Facade::create();
    if (isset($params['entry'])) {
      $entry = $map->getEntry($params['entry']);
      unset($params['entry']);
      // @todo if $entry is not object.
    } else {
      $entry = $map->getCurrentEntry();
    }
    
    $this->redirect('/'.$entry->uri($params));
  }
  
  public function previous()
  {
    return $this->storage->read('previous');
  }
  
  public function redirectToPrevious()
  {
    $this->redirect('/' . $this->previous());
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
    $this->view->assign($key, $value);
  }
  
  /**
   * read annotation
   * 
   * @param string $className class name
   * @param string $annotationName annotation name
   */
  protected function readAnnotation($className, $annotationName)
  {
    $anonr = Sabel_Annotation_Reader::create();
    $anonr->annotation($className);
    return $anonr->getAnnotationsByName($className, $annotationName);
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
    return $this->request->isPost();
  }
  
  protected function isGet()
  {
    return $this->request->isGet();
  }
  
  protected function success($uri)
  {
    Aspects_Validate::redirectWhenSuccess($uri);
  }
  
  protected function failure($uri)
  {
    Aspects_Validate::redirectWhenFailure($uri);
  }
  
  public function registAuthorizer($authorizer)
  {
    $this->security->registAuthorizer($authorizer);
  }
  
  public function authorize($identity, $password)
  {
    return $this->security->authorize($identity, $password);
  }
  
  public function unauthorize()
  {
    $this->security->unauthorize();
  }
  
  public function isAuthorized()
  {
    return $this->security->isAuthorized();
  }
}

class ReflectionCache
{
  private static $instance = null;
  protected $classes = array();
  protected $loaded = false;
  
  protected function __construct()
  {
    $path = RUN_BASE . '/cache/controller.cache';
    if (!$this->loaded && is_readable($path)) {
      $this->loaded = true;
      $this->classes = unserialize(file_get_contents($path));
    }
  }
  
  public static function create()
  {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }
  
  public function set($key, $val)
  {
    $this->classes[$key] = $val;
  }
  
  public function get($key)
  {
    return $this->classes[$key];
  }
  
  public function has($key)
  {
    return (isset($this->classes[$key]));
  }
  
  public function destruction()
  {
    $path = RUN_BASE . '/cache/controller.cache';
    file_put_contents($path, serialize($this->classes));
  }
}
