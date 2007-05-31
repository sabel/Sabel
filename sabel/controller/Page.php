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
    $hidden = array();
    
  protected
    $attributes  = array(),
    $assignments = array();
  
  protected
    $request  = null,
    $response = null,
    $storage  = null;
    
  protected
    $enableStorage = true;
    
  protected $destination = null;
  
  /**
   * reserved name lists of methods(actions)
   * @var array $reserved
   */
  private $reserved = array();
  
  /**
   * default constructer of page controller
   *
   */
  public final function __construct()
  {
    $ref = new ReflectionClass("Sabel_Controller_Page");
    $reserved = array();
    foreach ($ref->getMethods() as $name => $method) {
      $reserved[] = $method->getName();
    }
    
    $this->reserved = $reserved;
    $this->plugin = Sabel_Plugin::create($this);
    $injector = Sabel_Container::injector(new Factory());
    $this->response = $injector->newInstance("Sabel_Response");
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
  public function setup($request, $destination, $storage = null)
  {
    $this->request = $request;
    $this->destination = $destination;
    
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
      $response = $this->response;
      
      if (in_array($action, $this->reserved)) {
        return $response->notfound();
      }
      
      $proceed = $this->plugin->onBeforeAction();
      
      $isExistance = create_function
                     (
                       '$self, $action',
                       'return is_callable(array($self, $action));'
                     );
      
      if ($proceed) {
        if (method_exists($this, $action)) {
          $existance = $isExistance($this, $action);
        } else {
          $existance = false;
        }
        
        $callable = (!in_array($action, $this->hidden));
        
        if ($existance && $callable) {
          $response->success();
          $response->result = $this->$action();
          return $response;
        } elseif($this->isTemplateFound() && $callable) {
          return $response->success();
        } else {
          return $response->notfound();
        }
      }
      $this->plugin->onAfterAction();
      return $response;
    } catch (Exception $exception) {
      $this->plugin->onException($exception);
      echo $exception->getMessage();
    }
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
  public final function redirectTo($params)
  {
    /*
    if (!is_array($params) && is_string($params)) {
      $params = array(':action' => $params);
    }
    */
    
    $candidate = Sabel_Context::getCandidate();
    return $this->redirect($candidate->uri($this->convertParams($params)));
  }
  
  private function convertParams($param)
  {
    $buf = array();
    $params = explode(",", $param);
    $reserved = ";";
    foreach ($params as $part) {
      $line     = array_map("trim", explode(":", $part));
      $reserved = ($line[0] === 'n') ? "candidate" : $line[0];
      $buf[$reserved] = $line[1];
    }
    return $buf;
  }
  
  public final function isRedirected()
  {
    return $this->redirected;
  }
  
  public final function getRedirect()
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
  
  public final function getAttributes()
  {
    return $this->attributes;
  }
  
  public final function setAttribute($name, $value)
  {
    $this->attributes[$name] = $value;
  }
  
  public final function setAttributes($attributes)
  {
    $this->attributes = array_merge($attributes, $this->attributes);
  }
  
  public final function getResult()
  {
    return $this->result;
  }
  
  public final function getRequest()
  {
    return $this->request;
  }
  
  public function getResponse()
  {
    return $this->response;
  }
  
  public final function setAction($action)
  {
    $this->action = $action;
  }
  
  public final function getAction()
  {
    return $this->action;
  }
  
  public final function getStorage()
  {
    return $this->storage;
  }
  
  public final function getRequests()
  {
    return $this->request->getPostRequests();
  }
  
  public final function getAssignments()
  {
    return $this->assignments;
  }
  
  public final function hasRendered()
  {
    return (strlen($this->rendered) !== 0);
  }
  
  public final function getRendered()
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
  
  protected final function isTemplateFound()
  {
    $resource = Sabel_View_Locator_Factory::create()
                                            ->make($this->destination)
                                            ->locate($this->destination);
    return (!$resource->isMissing());
  }
}

class Sabel_Exception_ReservedActionName extends Sabel_Exception_Runtime {}
class Sabel_Exception_InvalidActionName  extends Sabel_Exception_Runtime {}
class Sabel_Exception_InvalidPlugin      extends Sabel_Exception_Runtime {}
