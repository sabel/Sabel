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
    
  private $context = null;
    
  public function __construct($request = null)
  {
    $injector = Sabel_Container::injector(new Factory());
    $this->context = new Sabel_Context();
    Sabel_Context::setContext($this->context);
    $this->context->setInjector($injector);
  }
  
  public function ignition($request = null, $storage = null)
  {
    l("[Core::Front] start of request -------------------");
    
    $context = $this->context;
    
    if ($request === null) {
      $builder = new Sabel_Request_Builder();
      $request = new Sabel_Request_Object();
      $builder->build($request);
    }
    
    $context->setRequest($request);
    $context->setLocale($context->getInjector()->newInstance("Sabel_Locale"));
    
    $router = new Sabel_Router_Map();
    $destination = $router->route($request, $context);
    $context->setDestination($destination);
    
    l("[Core::Front] request " . $request->__toString());
    
    Sabel_Plugin::load($context);
    Sabel_Helper::load($request, $destination);
    
    $injector = $context->getInjector();
    $executer = $injector->newInstance(self::EXECUTER_INTERFACE);
    $context->setExecuter($executer);
    $executer->setContext($context);
    $executer->setDestination($destination);
    
    try {
      $controller = $executer->create();
    } catch (Sabel_Exception_Runtime $e) {
      $destination->setModule(self::INDEX_PAGE);
      $destination->setController(self::INDEX_PAGE);
      $destination->setAction(self::NOT_FOUND_ACTION);
      $controller = $executer->create();
    }
    
    $response = $this->executeAction($executer, $request, $storage, $destination);
    if (!$response->hasController()) {
      $response->setController($controller);
    }
    
    l("[Core::Front] end of request -------------------\n");
    
    return $response;
  }
  
  /**
   * execute an action and output header
   *
   * @return Sabel_Response
   */
  public function executeAction($executer, $request, $storage, $destination)
  {
    $response = $executer->execute($request, $storage);
    
    $response->outputHeader();
    
    if ($response->isNotFound() || $response->isServerError()) {
      if ($response->isNotFound()) {
        $destination->setAction(self::NOT_FOUND_ACTION);
      } elseif ($response->isServerError()) {
        $destination->setAction(self::SERVER_ERROR_ACTION);
      }
      
      $response = $executer->execute($request, $storage);
      if ($response->isNotFound()) {
        $destination->setController(self::INDEX_PAGE);
        $controller = $executer->create();
        $response->setController($controller);
        $response = $executer->execute($request, $storage);
      }
    }
    
    $response->setDestination($destination);
    
    return $response;
  }
}
