<?php

Sabel::using("Sabel_Controller_Page_Base");
Sabel::using('Sabel_Storage_Session');
Sabel::using("Sabel_Logger_Factory");
Sabel::using('Sabel_Exception_Runtime');

/**
 * the Base of Page Controller.
 *
 * @todo remove dependency to Security package
 * @todo 
 * @todo 
 * @todo 
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
  
  protected
    $view       = null,
    $request    = null,
    $httpMethod = self::HTTP_METHOD_GET,
    $storage    = null,
    $logger     = null,
    $response   = null;
    
  protected
    $action        = '',
    $rendering     = true,
    $volatiles     = array(),
    $withLayout    = true,
    $attributes    = array(),
    $enableSession = true,
    $skipDefaultAction = true;
    
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
  public function setup(Sabel_Request $request, $view = null, $action)
  {
    $this->action  = $action;
    $this->request = $request;
    $this->logger  = Sabel_Logger_Factory::create("file");
    
    $this->view = ($view === null) ? Sabel::load('Sabel_View') : $view;
    Sabel_Context::setView($this->view);
    
    if ($this->enableSession) {
      $this->storage  = Sabel_Storage_Session::create();
    }
    
    if (isset($_SERVER['REQUEST_METHOD'])) {
      $this->httpMethod = $_SERVER['REQUEST_METHOD'];
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
  
  public function getAction()
  {
    return $this->action;
  }
  
  public function getRequests()
  {
    return $this->request->getPostRequests();
  }
  
  public function execute($actionName)
  {
    $this->action = $actionName;

    if (is_array($this->storage->read("volatiles"))) {
      $this->attributes = array_merge($this->storage->read("volatiles"), $this->attributes);
      foreach ($this->storage->read("volatiles") as $vname => $vvalue) {
        $this->storage->delete($vname);
      }
    }
    
    if (empty($actionName)) {
      throw new Sabel_Exception_InvalidActionName("invalid action name");
    }
    
    if (!headers_sent()) header('X-Framework: Sabel');
    $this->processModels();
    $this->processFilter($actionName, "before");
    
    // check reserved words
    if (isset($this->reserved[$actionName]))
      throw new Sabel_Exception_Runtime('use reserved action name');
    
    $result = $this->executeAction($actionName);
    
    $view = $this->view;
    $view->assign("request", $this->request);
    $view->assignByArray(Sabel_Context::getCurrentCandidate()->getElementVariables());
    $view->assignByArray($this->request->getPostRequests());
    $view->assignByArray($this->attributes);
    if (is_array($result)) $view->assignByArray($result);
    
    $this->processFilter($actionName, "after");
    $this->storage->write("volatiles", $this->volatiles);
    return $result;
  }
  
  protected function processModels()
  {
    if ($this->models !== null) {
      foreach ($this->models as $model) {
        $modelName = strtolower($model);
        $this->$modelName = MODEL($model);
      }
    }
  }
  
  protected function processFilter($actionName, $when = "around")
  {
    $filters = array_filter(array_keys(get_object_vars($this)),
                            create_function('$in', 'return (strstr($in, "filter"));'));
    
    asort($filters);
    foreach ($filters as $pos => $filterName) {
      $filter = $this->$filterName;
      if (isset($filter[$when])) {
        $this->doFilters($actionName, $filter[$when]);
      }
    }
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
        $this->logger->log("apply filter " . $filter);
        if ($this->$filter() === false) break;
      } else {
        throw new Sabel_Exception_Runtime($filter . " is not found in any actions");
      }
    }
  }
  
  protected function executeAction($action)
  {
    $reqMethod    = strtolower($this->httpMethod);
    $methodAction = $reqMethod . ucfirst($action);
    $actionResult = array();
    
    if ($this->hasMethod($methodAction)) {
      $this->logger->log("execute method action: $methodAction");
      $actionResult =(array) $this->$methodAction();
      if (!$this->skipDefaultAction) {
        $this->logger->log("execute action: $action");
        $actionResult = array_merge((array) $this->$action(), $actionResult);
      }
    } elseif ($this->hasMethod($action)) {
      $this->logger->log("execute action: $action");
      $actionResult = $this->$action();
    } elseif ($this->hasMethod('actionMissing')) {
      $this->actionMissing();
    }
    $result = (is_array($actionResult)) ? $actionResult : array();
    
    return $result;
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
        if ($this->$column !== null) $model->$column = $this->$column;
      }
    }
    
    return $model;
  }
  
  /**
   * create volatile memory in storage
   * this volatile data will be gone next end of action.
   *
   * @param string $key
   * @param mixed $value
   */
  protected function volatile($key, $value)
  {
    $this->storage->write($key, $value);
    $this->volatiles[$key] = $value;
    $this->logger->log("register volatile: $key");
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
    $this->storage->write("volatiles", $this->volatiles);
    $this->logger->log("redirect: $to");
    header($redirect);
    exit; // exit after HTTP Header(30x)
  }
  
  public function redirectTo($params)
  {
    if (!is_array($params) && is_string($params)) {
      $params = array(':action' => $params);
    }
    
    $candidate = Sabel_Context::getCurrentCandidate();
    $this->storage->write("volatiles", $this->volatiles);
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
