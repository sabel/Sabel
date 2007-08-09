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
  
  protected
    $result = null;
    
  protected
    $hidden = array();
    
  protected
    $attributes  = array(),
    $assignments = array();
    
  protected
    $request  = null,
    $response = null,
    $storage  = null;
    
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
  // public final function __construct($context)
  
  public final function __construct()
  {
    $reserved = get_class_methods("Sabel_Controller_Page");
    
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
    if (!$request instanceof Sabel_Request_Object) {
      throw new Sabel_Exception_Runtime("invalid request object");
    }
    
    if (!$destination instanceof Sabel_Destination) {
      throw new Sabel_Exception_Runtime("invalid destination object");
    }
    
    $this->request     = $request;
    $this->destination = $destination;
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
    if (!$this->request instanceof Sabel_Request_Object) {
      throw new Sabel_Exception_Runtime("invalid request object");
    }
    
    try {
      if ($this->request->isTypeOf("css")) {
        $this->response->setContentType("text/css");
        $this->disableLayout();
      }
      
      $response = $this->response;
      
      if (in_array($action, $this->reserved)) {
        return $response->notfound();
      }
      
      // $proceed = $this->plugin->onBeforeAction();
      
      $isExistance = create_function
                       ('$self, $action',
                        'return is_callable(array($self, $action));');
                     
      if (method_exists($this, $action)) {
        $existance = $isExistance($this, $action);
      } else {
        $existance = false;
      }
      
      $callable = (!in_array($action, $this->hidden));
      
      if ($existance && $callable) {
        $response->success();
        $response->result = $this->$action();
        // $this->plugin->onAfterAction();
        return $response;
      } elseif($this->isTemplateFound() && $callable) {
        // $this->plugin->onAfterAction();
        return $response->success();
      } else {
        if (ENVIRONMENT === DEVELOPMENT) {
          $this->assign("module",     $this->destination->getModule());
          $this->assign("controller", $this->destination->getController());
          $this->assign("action",     $this->destination->getAction());
        }
        
        // $this->plugin->onAfterAction();
        return $response->notfound();
      }
    } catch (Exception $exception) {
      // $this->plugin->onException($exception);
      l($exception->getMessage());
      return $response->serverError();
    }
  }
  
  public function executable($action)
  {
    $isExistance = create_function
                   (
                     '$self, $action',
                     'return is_callable(array($self, $action));'
                   );
                   
    if (method_exists($this, $action)) {
      $existance = $isExistance($this, $action);
    } else {
      $existance = false;
    }
    
    $callable = (!in_array($action, $this->hidden));
    
    if ($callable) {
      if ($existance) {
        return true;
      } elseif ($this->isTemplateFound()) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  /**
   * HTTP Redirect to another location.
   *
   * @access public
   * @param string $to /Module/Controller/Method
   * @return mixed self::REDIRECTED
   */
  public function redirect($to, $parameters = null)
  {
    if ($parameters !== null) {
      $buf = array();
      foreach ($parameters as $key => $value) {
        $buf[] = "{$key}={$value}";
      }
      $to .= "?" . join("&", $buf);
    }
    
    // $this->plugin->onRedirect($to);
    
    return self::REDIRECTED;
  }
  
  /**
   * HTTP Redirect to another location with uri.
   *
   * @param string $params
   */
  public final function redirectTo($destination, $parameters = null)
  {
    $candidate = $this->context->getCandidate();
    $uri = $candidate->uri($this->convertParams($destination));
    return $this->redirect($uri, $parameters);
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
  
  public final function getContext()
  {
    return $this->context;
  }
  
  public final function getRequests()
  {
    return $this->request->fetchPostValues();
  }
  
  public final function getAssignments()
  {
    return $this->assignments;
  }
  
  protected function __get($name)
  {
    if (array_key_exists($name, $this->attributes)) {
      return $this->attributes[$name];
    } else {
      return $this->request->find($name);
    }
  }
  
  protected function __set($name, $value)
  {
    $this->attributes[$name] = $value;
  }
  
  protected function __call($method, $arguments)
  {
    // return $this->plugin->call($method, $arguments);
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
