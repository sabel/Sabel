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
  public
    $plugin = null;
  
  protected
    $filters      = null,
    $controller   = null,
    $destination  = null,
    $request      = null;
    
  public function __construct($request = null)
  {
    if ($this->request === null) {
      $requestClass = Sabel_Const::REQUEST_CLASS;
      $this->request = new $requestClass();
    }
    
    // @todo renew for new map format
    Sabel::fileUsing(RUN_BASE . Sabel_Const::DEFAULT_MAP_FILE);
    
    $this->plugin = Sabel_Controller_Plugin::create();
  }
    
  public function ignition($storage = null)
  {
    Sabel_Context::log("request " . $this->request);
    Sabel_Context::initialize();
    
    $destination = $this->destination;
    
    Sabel_Helper::load($this->request, $destination);
    
    $this->processPreFilter();
    
    $executer = new Sabel_Controller_Executer_Flow($destination);
    $this->controller = $executer->create();
    
    $this->plugin->onCreateController($destination);
    
    $executer->execute($this->request, $storage);
    
    $this->processPostFilter();
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function processCandidate($request = null)
  {
    if ($request !== null) {
      $this->request = $request;
    }
    
    $candidate = new Sabel_Map_Candidate();
    $tokens    = new Sabel_Map_Tokens($this->request->__toString());
    $candidate = $candidate->find($tokens);
    
    Sabel_Context::setCurrentCandidate($candidate);
    $this->request->setCandidate($candidate);
    
    $this->destination = $candidate->getDestination();
    
    return $this;
  }
  
  private final function loadFilters()
  {
    if ($this->filters !== null) {
      return $this->filters;
    }
    
    $module = $this->destination->getModule();
    
    $sharedFiltersDir = RUN_BASE . "/app/filters";
    $filtersDir       = RUN_BASE . "/app/{$module}/filters";
    $filters = array();
    
    if (is_dir($sharedFiltersDir)) {
      if ($dh = opendir($sharedFiltersDir)) {
        while (($file = readdir($dh)) !== false) {
          if ($file{0} !== ".") {
            if (is_file($filtersDir . "/{$file}")) {
              // use derived class
              $filters[] = join("_", array(ucfirst($module),
                                           "Filters",
                                            str_replace(".php", "", $file)));
            } elseif (is_file($sharedFiltersDir . "/{$file}")) {
              $class = array('Filters', str_replace(".php", "", $file));
              $filters[] = join("_", $class);
            }
          }
        }
        closedir($dh);
      }
    }
    
    $this->filters = $filters;
    return $filters;
  }
  
  private final function processPreFilter()
  {
    $filters = $this->loadFilters();
    $request = $this->request;
    
    foreach ($filters as $filter) {
      $aFilter = Sabel::load($filter);
      $aFilter->setup($request)->execute();
      $aFilter->input();
    }
    
    return $filters;
  }
  
  private final function processPostFilter()
  {
    $filters = $this->loadFilters();
    $controller = $this->controller;
    
    foreach ($filters as $filter) {
      Sabel::load($filter)->output($controller);
    }
  }
  
  public function getResult()
  {
    $controller  = $this->controller;
    $destination = $this->destination;
    
    if ($controller->hasRendered()) {
      return $controller->getRendered();
    }
    
    $view = new Sabel_View();
    $view->assignByArray($controller->getRequests());
    
    $html = "";
    $assigns = array("assign" => array_merge($controller->getAssignments(),
                                             $controller->getAttributes()));
    
    try {
      $content = Sabel_View::render($destination, $assigns);
    } catch (Exception $e) {
      $content = "";
    }
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $html = $content;
    } else {
      $assign = array("assign" => array("contentForLayout" => $content));
      try {
        $content = Sabel_View::render($destination, $assigns);
        $d = clone $this->destination;
        $d->setAction(Sabel_Const::DEFAULT_LAYOUT);
        $html = Sabel_View::render($d, $assign);
      } catch (Exception $e) {
        $html = $content;
      }
    }
    
    return $html;
  }
}
