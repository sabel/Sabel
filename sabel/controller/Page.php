<?php

uses('sabel.db.BaseClasses');
uses('sabel.template.Director');
uses('sabel.template.Engine');

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
  }
  
  protected function setup()
  {
    $this->container   = new Sabel_Container_DI();
    $this->request     = new Sabel_Request_Request($this->entry);
    $this->destination = $this->entry->getDestination();
    
    $this->setupLogger();
    $this->setupResponse();
    $this->setTemplate(new HtmlTemplate());
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
    if (method_exists($this, $name)) {
      return true;
    } else {
      return false;
    }
  }

  protected function setTemplate($template)
  {
    $this->template = $template;
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
    $ref = $_SERVER['HTTP_REFERER'];
    $replaced = preg_replace('/\\//', '\/', $validURIs[0]);
    $patternAbsoluteURI = '/http:\/\/' . $_SERVER['HTTP_HOST'].$replaced.'/';
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
    $absolute  = 'http://' . $_SERVER['HTTP_HOST'];
    $redirect  = 'Location: ' . $absolute . $to;
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
    $d = TemplateDirectorFactory::create(null, $this->destination);
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