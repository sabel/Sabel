<?php

/**
 * Sabel_Controller_Front_Base
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Controller_Front_Base
{
  public
    $plugin = null;
  
  protected
    $candidate = null,
    $request   = null, 
    $requestClass = "Sabel_Request_Web";
    
  protected 
    $controller = null;
  
  public function ignition($storage = null)
  {
    Sabel_Context::log("request " . $this->request);
    Sabel_Context::initialize();
    
    $filters = $this->loadFilters($this->candidate);
    $this->processHelper($this->request, $this->candidate);
    $this->processPreFilter($filters, $this->request);
    $controller = $this->processPageController($this->candidate);
    $this->plugin->onCreateController($controller, $this->candidate);
    $controller->setup($this->request, $storage);
    $actionName = $this->candidate->getAction();
    $controller->setAction($actionName);
    $controller->initialize();
    $this->processPostFilter($filters, $controller);
    $controller->execute($actionName);
    $this->controller = $controller;
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function getResult()
  {
    return $this->processView($this->controller);
  }
  
  abstract public function processCandidate($request = null);
  abstract protected function loadFilters($candidate);
  abstract protected function processHelper($request, $candidate);
  abstract protected function processPreFilter($filters, $request);
  abstract protected function processPageController($candidate);
  abstract protected function processPostFilter($filters, $controller);
  abstract protected function processView($controller);
}
