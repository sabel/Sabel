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
    $this->container   = Container::create();
    $this->request     = $this->entry->getRequest();
    $this->requests    = $this->request->requests();
    $this->storage     = Sabel_Storage_Session::create();
    $this->destination = $this->entry->getDestination();
  }
  
  public function getRequests()
  {
    return $this->request->requests();
  }
  
  public function execute()
  {
    $actionName = $this->destination->action;
    $this->methodExecute($actionName);
    return Sabel_Template_Engine::getAttributes();
  }
  
  protected function __get($name)
  {
    if ($this->request->hasUriValue($name))
      return $this->request->$name;
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
    $refClass = new ReflectionClass($this);
    
    $httpMethods = array('get', 'post', 'put', 'delete');
    foreach ($httpMethods as $method) {
      $checkMethod = 'is'.ucfirst($method);
      $actionName = $method.ucfirst($action);
      if ($this->$checkMethod() && $refClass->hasMethod($actionName)) {
        $action = $actionName;
      }
    }
    $this->$action();
    $this->storage->write('previous', $this->request->__toString());
  }
  
  protected function hasMethod($name)
  {
    return (method_exists($this, $name));
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
    return new Sabel_Aspect_DynamicProxy($target);
  }
  
  /**
   * HTTP Redirect to another location.
   * this method will avoid "back button" problem.
   *
   * @param string $to /Module/Controller/Method
   */
  protected function redirect($to)
  {
    $host = Sabel_Env_Server::create()->http_host;
    $absolute = 'http://' . $host;
    $redirect = 'Location: ' . $absolute . $to;
    header($redirect);
    
    exit; // exit after HTTP Header(30x)
  }
  
  protected function redirectTo($params)
  {
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
  
  protected function previous()
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
  
  protected function assign($key, $value)
  {
    Sabel_Template_Engine::setAttributes($key, $value);
  }
}