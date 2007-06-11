<?php

/**
 * Sabel_Controller_Front
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_Controller_Front
{
  const REQUEST_INTERFACE   = "Sabel_Request";
  const EXECUTER_INTERFACE  = "Sabel_Controller_Executer";
  const NOT_FOUND_ACTION    = "notFound";
  const SERVER_ERROR_ACTION = "serverError";
  const INDEX_PAGE          = "index";
  
  private
    $request  = null,
    $response = null;
  
  protected
    $injector    = null,
    $controller  = null,
    $destination = null;
    
  public function __construct($request = null)
  {
    $this->injector = Sabel_Container::injector(new Factory());
    Sabel_Context::setDefaultInjector($this->injector);
  }
  
  public function ignition($request = null, $storage = null)
  {
    if ($request === null) {
      $builder = new Sabel_Request_Builder();
      $request = new Sabel_Request_Object();
      $builder->build($request);
    }
    
    $this->request = $request;
    
    $router = new Sabel_Router_Map();
    $destination = $this->destination = $router->route($this->request);
    
    Sabel_Context::log("request " . $this->request->__toString());
    Sabel_Context::initialize();
    Sabel_Context::setDestination($destination);
    
    Sabel_Plugin::create()->setDestination($destination);
    Sabel_Helper::load($this->request, $destination);
    
    $executer = $this->injector->newInstance(self::EXECUTER_INTERFACE);
    $executer->setDestination($destination);

    try {
      $this->controller = $executer->create();
    } catch (Sabel_Exception_Runtime $e) {
      $destination->setModule(self::INDEX_PAGE);
      $destination->setController(self::INDEX_PAGE);
      $destination->setAction(self::NOT_FOUND_ACTION);
      $this->controller = $executer->create();
    }
    
    $response = $executer->execute($this->request, $storage);
    
    if ($response->isNotFound() || $response->isServerError()) {
      $response->outputHeader();
      if ($response->isNotFound()) {
        $destination->setAction(self::NOT_FOUND_ACTION);
      } elseif ($response->isServerError()) {
        $destination->setAction(self::SERVER_ERROR_ACTION);
      }
      $response = $executer->execute($this->request, $storage);
      if ($response->isNotFound()) {
        $destination->setController(self::INDEX_PAGE);
        $this->controller = $executer->create();
        $response = $executer->execute($this->request, $storage);
      }
    }
    
    $this->response = $response;
    $response->setController($this->controller);
    $response->setDestination($this->destination);
    
    return $response;
  }
  
  public function getRequest()
  {
    return $this->request;
  }
  
  public function getResponse()
  {
    return $this->response;
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function getDestination()
  {
    return $this->destination;
  }
}
