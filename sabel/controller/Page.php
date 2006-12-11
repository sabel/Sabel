<?php

Sabel::using('Sabel_Security_Security');
Sabel::using('Sabel_Security_Permission');
Sabel::using('Sabel_Storage_Session');
$a = 'abc';
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
    $request     = null,
    $httpMethod  = 'GET',
    $requests    = array(),
    $storage     = null,
    $response    = null;
    
  protected
    $public     = array(),
    $private    = array(),
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
  private $reserved = array('setup',
                            'getAction',
                            'getRequests',
                            'execute',
                            'initialize');
  
  public function initialize()
  {
    // none.
  }
  
  public function setup($request, $view = null)
  {
    if ($view === null) {
      $this->view = Sabel::load('Sabel_View');
    } else {
      $this->view = $view;
    }
    
    Sabel_Context::setView($this->view);
    
    $this->request  = $request;
    $this->requests = $this->requests();
    
    if ($this->enableSession) {
      $this->storage  = Sabel_Storage_Session::create();
      $this->security = Sabel_Security_Security::create();
      $this->identity = $this->security->getIdentity();
    }
    
    if (isset($_SERVER['REQUEST_METHOD'])) {
      $this->httpMethod = $_SERVER['REQUEST_METHOD'];
    }
  }
  
  protected function __get($name)
  {
    $candidate = Sabel_Context::getCurrentCandidate();
    if ($candidate->hasElementVariableByName($name)) {
      return $candidate->getElementVariableByName($name);
    } else {
      return $this->request->getRequestValue($name);
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
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function getRequests()
  {
    return $this->request->requests();
  }
  
  public function execute($actionName)
  {
    if (!headers_sent()) header('X-Framework: Sabel');
    
    // check reserved words
    if (isset($this->reserved[$actionName]))
      throw new Sabel_Exception_Runtime('use reserved action name');
      
    $result = null;
    if ($this->isPublicAction($actionName)) {
      $result = $this->methodCheckAndExecute($actionName);
    } elseif ($this->permission === Sabel_Security_Permission::P_PRIVATE ||
          $this->isPrivateAction($actionName)) {
      if ($this->isAuthorized()) {
        $result = $this->methodCheckAndExecute($actionName);
      } elseif ($this->hasMethod('authorizeRequired')) {
        $this->authorizeRequired();
      } else {
        throw new Sabel_Exception_Runtime('must implement authorizeRequired() when P_PRIVATE');
      }
    } else {
      $result = $this->methodCheckAndExecute($actionName);
    }
    
    if (is_array($result)) $this->view->assignByArray($result);
    return $result;
  }
  
  protected function methodCheckAndExecute($action)
  {
    $reqMethod    = strtolower($this->httpMethod);
    $actionName   = $reqMethod . ucfirst($action);
    $actionResult = array();
    
    if ($this->hasMethod($actionName)) {
      $actionResult =(array) $this->methodExecute($actionName);
      if (!$this->skipDefaultAction) {
        $actionResult = array_merge((array) $this->methodExecute($action), $actionResult);
      }
    } elseif ($this->hasMethod($action)) {
      $actionResult = $this->methodExecute($action);
    } elseif ($this->hasMethod('actionMissing')) {
      $this->actionMissing();
    }
    $result = (is_array($actionResult)) ? $actionResult : array();
    
    if (is_object($this->storage))
      $this->storage->write('previous', $this->request->__toString());
      
    return $result;
  }
  
  protected function methodExecute($action)
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
        throw Sabel::load('Sabel_Exception_TemplateMissing', var_export($this->view, 1));
      }
    } else {
      return $this->view->rendering();
    }
  }
  
  protected function isPublicAction($actionName)
  {
    if (count($this->public) > 0) {
      return in_array($actionName, $this->public);
    } else {
      return false;
    }
  }
  
  protected function isPrivateAction($actionName)
  {
    if (count($this->private) > 0) {
      return in_array($actionName, $this->private);
    } else {
      return false;
    }
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
    $host = $_SERVER['HTTP_HOST'];
    $ref  = $_SERVER['HTTP_REFERER'];
    $patternAbsoluteURI = '%http://' . $host . $validURIs[0]. '%';
    return (bool) preg_match($patternAbsoluteURI, $ref);
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
    $host = $_SERVER['HTTP_HOST'];
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . $to;
    header($redirect);
    
    exit; // exit after HTTP Header(30x)
  }
  
  public function redirectTo($params)
  {
    if (!is_array($params) && is_string($params)) {
      $params = array(':action' => $params);
    }
    
    $candidate = Sabel_Context::getCurrentCandidate();
    $this->redirect('/' . $candidate->uri($params));
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
  
  /*
  protected function readAnnotation($className, $annotationName)
  {
    $anonr = Sabel_Annotation_Reader::create();
    $anonr->annotation($className);
    return $anonr->getAnnotationsByName($className, $annotationName);
  }
  */
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
