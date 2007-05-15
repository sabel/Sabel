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
    $controller   = null,
    $destination  = null,
    $request      = null,
    $requestClass = "Sabel_Request_Web";
    
  public function __construct($request = null)
  {
    if ($this->request === null) {
      $this->request = new $this->requestClass();
    }
    
    // @todo renew for new map format
    Sabel::fileUsing(RUN_BASE . '/config/map.php');
    
    $this->plugin = Sabel_Controller_Plugin::create();
  }
    
  public function ignition($storage = null)
  {
    Sabel_Context::log("request " . $this->request);
    Sabel_Context::initialize();
    
    $destination = $this->destination;
    
    $filters = $this->loadFilters();
    $this->processHelper($this->request);
    $this->processPreFilter($filters, $this->request);
    
    $executer = new Sabel_Controller_Executer($destination);
    $this->controller = $executer->create();
    $this->plugin->onCreateController($this->controller, $destination);
    
    $executer->execute($this->request, $storage);
    
    $this->processPostFilter($filters);
  }
  
  public function getController()
  {
    return $this->controller;
  }
  
  public function getResult()
  {
    return $this->processView($this->controller);
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
    
    return $filters;
  }
  
  private final function processHelper($request)
  {
    list($m, $c, $a) = $this->destination->toArray();
    
    $appDir       = "app";
    $helperDir    = "helpers";
    $sharedHelper = "application";
    $helperSuffix = "php";
    
    $pref = "{$appDir}/{$m}/{$helperDir}/";
    $helpers = array("/{$appDir}/{$helperDir}/{$sharedHelper}.{$helperSuffix}",
                     $pref . "{$sharedHelper}.{$helperSuffix}",
                     $pref . "{$c}.{$helperSuffix}",
                     $pref . "{$c}.{$a}.{$helperSuffix}");
                     
    foreach ($helpers as $helper) {
      $path = RUN_BASE . $helper;
      if (is_file($path)) Sabel::fileUsing($path);
    }
  }
  
  private final function processPreFilter($filters, $request)
  {
    foreach ($filters as $filter) {
      $aFilter = Sabel::load($filter);
      $aFilter->setup($request)->execute();
      $aFilter->input();
    }
    
    return $filters;
  }
  
  private final function processPostFilter($filters)
  {
    $controller = $this->controller;
    foreach ($filters as $filter) {
      Sabel::load($filter)->output($controller);
    }
  }
  
  private final function processView()
  {
    $controller = $this->controller;
    if ($controller->hasRendered()) {
      return $controller->getRendered();
    }
    
    $view = new Sabel_View();
    $view->assignByArray($controller->getRequests());
    
    $html = "";
    $assigns = array("assign" => array_merge($controller->getAssignments(),
                                             $controller->getAttributes()));
    
    try {
      $content = Sabel_View::render(null, $assigns);
    } catch (Exception $e) {
      $content = "";
    }
    
    if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])) {
      $html = $content;
    } else {
      $assign = array("assign" => array("contentForLayout" => $content));
      try {
        $content = Sabel_View::render(null, $assigns);
        $html = Sabel_View::render(Sabel_Const::DEFAULT_LAYOUT, $assign);
      } catch (Exception $e) {
        $html = $content;
      }
    }
    
    return $html;
  }
}
