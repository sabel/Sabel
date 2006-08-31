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
    $response    = null,
    $template    = null,
    $container   = null,
    $destination = null;
  
  public function __construct($entry)
  {
    $this->entry = $entry;
    $this->setup();
    $this->initialize();
  }
  
  protected function initialize()
  {
    // none.
  }
  
  protected function setup()
  {
    $this->container = Sabel_Container_DI::create();
    $this->request   = new Sabel_Request_Request($this->entry);
    $this->response  = new Sabel_Response_Web();
    $this->destination = $this->entry->getDestination();
    $this->logger = $this->container->load('Sabel_Logger_File');
  }
  
  public function execute()
  {
    $actionName = $this->destination->action;
    $this->methodExecute($actionName);
    
    $template = Sabel_Template_Service::create($this->entry);
    $template->assignByArray($this->response->responses());
    $template->rendering();
  }
  
  protected function __get($name)
  {
    if ($this->request->hasUriValue($name)) {
      return $this->request->$name;
    }
  }
  
  protected function __call($method, $args)
  {
    if ($this->request->hasMethod($method)) {
      return $this->request->$method($args);
    }
  }
  
  protected function methodExecute($action)
  {
    $controllerClass = $this->destination->module.'_'.$this->destination->controller;
    $refClass = new ReflectionClass($controllerClass);
    
    $httpMethods = array('get', 'post', 'put', 'delete');
    foreach ($httpMethods as $method) {
      $checkMethod = 'is'.ucfirst($method);
      $actionName = $method.ucfirst($action);
      if ($this->$checkMethod && $refClass->hasMethod($actionName)) {
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