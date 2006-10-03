<?php

/**
 * page controller base class.
 *
 * @author Mori Reo <mori.reo@gmail.com>
 * @package sabel.controller
 */
abstract class Sabel_Controller_Page
{
  protected
    $entry       = null,
    $cache       = null,
    $logger      = null,
    $request     = null,
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
    $this->response    = new Sabel_Response_Web();
    $this->storage     = Sabel_Storage_Session::create();
    $this->destination = $this->entry->getDestination();
  }
  
  public function execute()
  {
    $actionName = $this->destination->action;
    $this->methodExecute($actionName);
    return Re::get();
  }
  
  protected function __get($name)
  {
    if ($this->request->hasUriValue($name)) {
      return $this->request->$name;
    }
  }
  
  protected function __set($name, $value)
  {
    Re::set($name, $value);
  }
  
  protected function __call($method, $args)
  {
    if ($this->request->hasMethod($method)) {
      return $this->request->$method($args);
    }
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
  
  /**
   * forwaring anothor controller or method of same controller.
   *
   */
  protected function forward($to)
  {
    // @todo implemen
  }
}