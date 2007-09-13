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
  protected
    $bus      = null,
    $request  = null,
    $response = null,
    $storage  = null;

  protected
    $setup       = false,
    $hidden      = array(),
    $attributes  = array(),
    $assignments = array(),
    $destination = null;
  
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
    $this->storage     = $storage;
    $this->setup = true;
  }
  
  public function setBus($bus)
  {
    $this->bus = $bus;
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
    if (!$this->setup) {
      throw new Sabel_Exception_Runtime("page controller must be setup");
    }
    
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
        return $response;
      } elseif($this->isTemplateFound() && $callable) {
        return $response->success();
      } else {
        if (ENVIRONMENT === DEVELOPMENT) {
          $this->assign("module",     $this->destination->getModule());
          $this->assign("controller", $this->destination->getController());
          $this->assign("action",     $this->destination->getAction());
        }
        
        return $response->notfound();
      }
    } catch (Exception $exception) {
      l($exception->getMessage());
      $this->exception = $exception;
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
   * assign value to template.
   *
   * @param mixed $key search key
   * @param mixed $value value
   */
  protected function assign($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public final function getAttribute($name)
  {
    if (isset($this->attributes[$name])) {
      return $this->attributes[$name];
    } else {
      return null;
    }
  }
  
  public final function setAttribute($name, $value)
  {
    $this->attributes[$name] = $value;
  }
  
  public final function getAttributes()
  {
    return $this->attributes;
  }
  
  public final function setAttributes($attributes)
  {
    $this->attributes = array_merge($attributes, $this->attributes);
  }
  
  public final function hasAttribute($name)
  {
    return isset($this->attributes[$name]);
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
    return $this->request->fetchPostValues();
  }
  
  public function getResponse()
  {
    return $this->response;
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
