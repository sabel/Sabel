<?php

uses('sabel.db.BaseClasses');
uses('sabel.template.Re');
uses('sabel.template.Form');

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
    $this->container   = new Sabel_Container_DI();
    $this->request     = new Sabel_Request_Request($this->entry);
    $this->destination = $this->entry->getDestination();
    
    $this->setupLogger();
    $this->setupResponse();
    $this->template = Sabel_Template_Service::create();
  }
  
  public function execute()
  {
    $actionName = $this->destination->action;
    $this->methodExecute($actionName);
    $this->initTemplate();
    $this->assignTemplates();
    $this->showTemplate();
  }
  
  protected function __get($name)
  {
    return $this->request->$name;
  }
  
  protected function requests()
  {
    return $this->request->requests();
  }
  
  protected function isPost()
  {
    return $this->request->isPost();
  }
  
  protected function isGet()
  {
    return $this->request->isGet();
  }
  
  protected function isPut()
  {
    return $this->request->isPut();
  }
  
  protected function isDelete()
  {
    return $this->request->isDelete();
  }
  
  protected function setupLogger()
  {
    $this->logger = $this->container->load('Sabel_Logger_File');
  }
  
  protected function setupResponse()
  {
    $this->response = new Sabel_Response_Web();
  }
  
  protected function methodExecute($action)
  {
    $controllerClass = $this->destination->module.'_'.$this->destination->controller;
    $refClass = new ReflectionClass($controllerClass);
    
    if ($this->isPost() && $refClass->hasMethod('post'.ucfirst($action))) {
      $action = 'post'.ucfirst($action);
    } else if ($this->isGet() && $refClass->hasMethod('get'.ucfirst($action)){
      $action = 'get'.ucfirst($action);
    } else if ($this->isPut() && $refClass->hasMethod('put'.ucfirst($action)) {
      $action = 'put'.ucfirst($action);
    } else if ($this->isDelete() && $refClass->hasMethod('delete'.ucfirst($action)) {
      $action = 'delete'.ucfirst($action);
    }
    
    $refMethod = new ReflectionMethod($controllerClass, $action);
    
    $hasClass = false;
    foreach ($refMethod->getParameters() as $paramidx => $parameter) {
      $requireParameterClass = 
                  ($reflectionClass = $parameter->getClass()) ? true : false;
                  
      if ($requireParameterClass) {
        $hasClass = true;
        $this->container = new SabelDIContainer();
        $object = $this->container->load($reflectionClass->getName());
      }
    }
    
    if ($hasClass) {
      $this->$action($object);
    } else {
      $this->$action();
    }
    
  }
  
  protected function assignTemplates()
  {
    foreach ($this->response->responses() as $key => $val)
      $this->template->assign($key, $val);
  }

  protected function hasMethod($name)
  {
    return (method_exists($this, $name));
  }
  
  protected function getActionMethods()
  {
    $methods = get_class_methods($this);

    $ar = array();
    foreach ($methods as $key => $val) {
      if ($val[0] != '_') {
        $ar[$key] = $val;
      }
    }
    return $ar;
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

  /**
   * initialize template
   */
  protected function initTemplate()
  {
    $d = Sabel_Template_Director_Factory::create($this->entry);
    $this->template->selectPath($d->decidePath());
    $this->template->selectName($d->decideName());
  }

  /**
   * process template then rendering it.
   *
   */
  protected function showTemplate()
  {
    try {
      $this->template->rendering();
    } catch(SabelException $e) {
      $e->printStackTrace();
    }
  }
}