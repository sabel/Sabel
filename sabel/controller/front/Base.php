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
  
  public function ignition($storage = null)
  {
    header("Content-Type: text/html; charset=UTF-8");
    Sabel_Context::log("request " . $this->request);
    
    $view = new Sabel_View();
    Sabel_Context::setView($view);
    
    $filters = $this->loadFilters($this->candidate);
    
    $this->processHelper($this->request, $this->candidate);
    $this->processPreFilter($filters, $this->request);
    
    $controller = $this->processPageController($this->candidate);
    $this->plugin->onCreateController($controller, $this->candidate);
    
    $controller->setup($this->request, $view, $storage);
    
    $actionName = $this->candidate->getAction();
    $controller->setAction($actionName);
    
    $controller->initialize();
    $this->processPostFilter($filters, $controller);
        
    $result = $controller->execute($actionName);
    
    $assignments = $controller->getAssignments();
    $view->assignByArray($assignments);
    
    $condition = new Sabel_View_Locator_Condition(true);
    $condition->setCandidate($this->candidate);
    
    $locator   = new Sabel_View_Locator_File();
    $resources = $locator->locate($condition);
    
    $content   = $view->rendering($resources->template);
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $html = $content;
    } else {
      $view->assign("contentForLayout", $content);
      $html = $view->rendering($resources->layout);
    }
    
    return $html;
  }
 
  abstract public function processCandidate($request = null);
  abstract protected function loadFilters($candidate);
  abstract protected function processHelper($request, $candidate);
  abstract protected function processPreFilter($filters, $request);
  abstract protected function processPageController($candidate);
  abstract protected function processPostFilter($filters, $controller);
}
