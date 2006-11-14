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
    
    $entry       = null,
    $cache       = null,
    $logger      = null,
    $request     = null,
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
    $pageMethods = array();
    foreach ($methods as $method) {
      $reserved[$method->getName()] = 1;
    }
    $this->reserved = $reserved;
  }
  
  public function setEntry($entry)
  {
    $this->entry = $entry;
  }
  
  public function initialize()
  {
    // none.
  }
  
  public function setup()
  {
    $this->security    = Sabel_Security_Security::create();
    $this->identity    = $this->security->getIdentity();
    $this->container   = Container::create();
    $this->request     = $this->entry->getRequest();
    $this->requests    = $this->request->requests();
    $this->storage     = Sabel_Storage_Session::create();
    $this->destination = $this->entry->getDestination();
    $this->action      = $this->destination->action;
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
    $actionName = $this->destination->action;
    if (isset($this->reserved[$actionName]))
      throw new Sabel_Exception_Runtime('use reserved action name');
    
    if ($this->isPublicAction($actionName)) {
      $this->methodExecute($actionName);
      return Sabel_Template_Engine::getAttributes();
    }
    
    if ($this->permission === Sabel_Security_Permission::P_PRIVATE) {
      if ($this->isAuthorized()) {
        $this->methodExecute($actionName);
        return Sabel_Template_Engine::getAttributes();
      } else if ($this->hasMethod('authorizeRequired')) {
        $this->authorizeRequired();
        return false;
      } else {
        throw new Sabel_Exception_Runtime('must implement authorizeRequired() when P_PRIVATE');
      }
    } else {
      $this->methodExecute($actionName);
      return Sabel_Template_Engine::getAttributes();
    }
  }
  
  protected function isPublicAction($actionName)
  {
    $ref = new ReflectionClass($this);
    $annot = $this->readAnnotation($ref->getName(), $actionName);
    if (isset($annot[0]) && is_object($annot[0])) {
      $annot = $annot[0];
      return ($annot->getContents() === 'public');
    } else {
      return false;
    }
  }
  
  protected function isPrivateAction($actionName)
  {
    $ref = new ReflectionClass($this);
    $annot = $this->readAnnotation($ref->getName(), $actionName);
    if (isset($annot[0]) && is_object($annot[0])) {
      $annot = $annot[0]; 
      return ($annot->getContents() === 'private');
    }
  }
  
  protected function __get($name)
  {
    if ($this->request->hasUriValue($name)) {
      return $this->request->$name;
    } else {
      $tmp = $this->getRequests();
      return $tmp[$name];
    }
  }
  
  protected function isNull($name)
  {
    if ($this->request->hasUriValue($name)) {
      $check = $this->request->$name;
    } else {
      $tmp = $this->getRequests();
      $check = $tmp[$name];
    }
    
    return (is_null($check));
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
  
  protected function methodExecute($action)
  {
    $specificAction = false;
    $refClass = new ReflectionClass($this);
    
    $httpMethods = array('get', 'post', 'put', 'delete');
    foreach ($httpMethods as $method) {
      $checkMethod = 'is'.ucfirst($method);
      $actionName = $method.ucfirst($action);
      if ($this->$checkMethod() && $refClass->hasMethod($actionName)) {
        $specificAction = $actionName;
      }
    }
    
    if ($specificAction !== false) {
      if ($this->hasMethod($specificAction)) {
        $this->$specificAction();
        if (!$this->skipDefaultAction) {
          $this->$action();
        }
      } elseif ($this->hasMethod('actionMissing')) {
        $this->actionMissing();
      }
    } else {
      if ($this->hasMethod($action)) {
        $this->$action();
      } elseif ($this->hasMethod('actionMissing')){
        $this->actionMissing();
      }
    }
    
    $this->storage->write('previous', $this->request->__toString());
  }
  
  public function hasMethod($name)
  {
    return (method_exists($this, $name));
  }
  
  public function successMethod()
  {
    return 'success'.ucfirst($this->getAction());
  }
  
  public function hasSuccessMethod()
  {
    return ($this->hasMethod($this->successMethod()));
  }
  
  public function errorMethod()
  {
    return 'error'.ucfirst($this->getAction());
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
    Sabel_Template_Service::setLayout($layout);
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
    Sabel_Template_Engine::setAttribute($key, $value);
  }
  
  /**
   * read annotation
   * 
   * @param string $className class name
   * @param string $annotationName annotation name
   */
  protected function readAnnotation($className, $annotationName)
  {
    $anonr = create('sabel.annotation.Reader');
    $anonr->annotation($className);
    return $anonr->getAnnotationsByName($className, $annotationName);
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
