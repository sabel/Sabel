<?php

Sabel::using('Sabel_Security_Security');
Sabel::using('Sabel_Security_Permission');
Sabel::using('Sabel_Storage_Session');

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
    $action        = '',
    $filters       = array(),
    $rendering     = true,
    $withLayout    = true,
    $attributes   = array(),
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
    $this->view = ($view === null) ? Sabel::load('Sabel_View') : $view;
    
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
    if (isset($this->attributes[$name])) {
      return $this->attributes[$name];
    } elseif ($candidate->hasElementVariableByName($name)) {
      return $candidate->getElementVariableByName($name);
    } elseif (is_object($this->request->getParameters()) && $this->request->hasParameter($name)) {
      return $this->request->getParameter($name);
    } else {
      return $this->request->getRequestValue($name);
    }
  }
  
  protected function __set($name, $value)
  {
    $this->attributes[$name] = $value;
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
    
    $this->processFilter($actionName, "before");
    
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
    
    $view = $this->view;
    $view->assignByArray($this->requests);
    $view->assign("candidate" , Sabel_Context::getCurrentCandidate());
    $view->assign("request" ,   $this->request);
    $view->assign("parameter",  $this->request->getParameters());
    $view->assignByArray($this->attributes);
    if (is_array($result)) $view->assignByArray($result);
    
    $this->processFilter($actionName, "after");
    
    return $result;
  }
  
  protected function processFilter($actionName, $when = "around")
  {
    if (isset($this->filters[$when]))
      $this->doFilters($actionName, $this->filters[$when]);
  }
  
  protected function doFilters($actionName, $filters)
  {
    if (isset($filters["exclude"]) && isset($filters["include"])) {
      throw new Sabel_Exception_Runtime("exclude and include can't define in same time");
    }
    
    if (isset($filters["exclude"])) {
      if (in_array($actionName, $filters["exclude"])) {
        return false;
      } else {
        unset($filters["exclude"]);
        $this->executeFilters($filters);
      }
    } elseif (isset($filters["include"])) {
      if (in_array($actionName, $filters["include"])) {
        unset($filters["include"]);
        $this->executeFilters($filters);
      }
    } else {
      $this->executeFilters($filters);
    }
  }
  
  protected function executeFilters($filters)
  {
    if (0 === count($filters)) return;
    
    foreach ($filters as $filter) {
      if ($this->hasMethod($filter)) {
        if ($this->$filter() === false) break;
      } else {
        throw new Sabel_Exception_Runtime($filter . " is not found in any actions");
      }
    }
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
      if ($this->hasMethod("render")) {
        $rendered = $this->view->rendering($this->withLayout);
        return $this->render($rendered);
      } else {
        return $this->view->rendering($this->withLayout);
      }
    }
  }
  
  protected function fill($model, $options = null)
  {
    if (!$model instanceof Sabel_DB_Model) {
      throw new Sabel_Exception_Runtime("model isn't Sabel_DB_Model");
    }
    
    if ($options === null) $options = array("ignores"=>array());
    
    foreach ($model->getColumnNames() as $column) {
      if (!in_array($column, $options["ignores"])) {
        $model->$column = $this->$column;
      }
    }
    
    return $model;
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
