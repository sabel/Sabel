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
final class Sabel_Controller_Front extends Sabel_Controller_Front_Base
{
  public function __construct($request = null)
  {
    if ($this->request === null) {
      $this->request = new $this->requestClass();
    }
    
    if (ENVIRONMENT === PRODUCTION) {
      $cache = Sabel_Cache_Manager::create();
      if (!($candidates = $cache->read("map_candidates"))) {
        Sabel::fileUsing(RUN_BASE . '/config/map.php');
        $serialized = serialize(Sabel_Map_Configurator::getCandidates());
        $cache->write("map_candidates", $serialized);
      } else {
        Sabel_Map_Configurator::setCandidates(unserialize($candidates));
      }
    } else {
      Sabel::fileUsing(RUN_BASE . '/config/map.php');
    }
    
    $this->plugin = Sabel_Controller_Plugin::create();
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
    
    $this->candidate = $candidate;
    
    return $this;
  }
  
  protected function loadFilters($candidate)
  {
    $sharedFiltersDir = RUN_BASE . "/app/filters";
    $filtersDir       = RUN_BASE . "/app/{$candidate->getModule()}/filters";
    $filters = array();
    
    if (is_dir($sharedFiltersDir)) {
      if ($dh = opendir($sharedFiltersDir)) {
        while (($file = readdir($dh)) !== false) {
          if ($file{0} !== ".") {
            if (is_file($filtersDir . "/{$file}")) {
              // use derived class
              $filters[] = join("_", array(ucfirst($candidate->getModule()),
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
  
  protected function processHelper($request, $candidate)
  {
    $module = $candidate->getModule();
    $cntr   = $candidate->getController();
    $action = $candidate->getAction();
    
    $appDir          = "app";
    $helperDirName   = "helpers";
    $appSharedHelper = "application";
    $helperPrefix    = "php";
    
    $helpers = array("/{$appDir}/{$helperDirName}/{$appSharedHelper}.{$helperPrefix}",
                     "/{$appDir}/{$module}/{$helperDirName}/{$appSharedHelper}.{$helperPrefix}",
                     "/{$appDir}/{$module}/{$helperDirName}/{$cntr}.{$helperPrefix}",
                     "/{$appDir}/{$module}/{$helperDirName}/{$cntr}.{$action}.{$helperPrefix}");
                     
    foreach ($helpers as $helper) {
      $path = RUN_BASE . $helper;
      if (is_file($path)) Sabel::fileUsing($path);
    }
  }
  
  protected function processPreFilter($filters, $request)
  {
    foreach ($filters as $filter) {
      $aFilter = Sabel::load($filter);
      $aFilter->setup($request)->execute();
      $aFilter->input();
    }
    
    return $filters;
  }
  
  protected function processPageController($candidate)
  {
    $classpath  = $candidate->getModule();
    $classpath .= '_' . trim(Sabel_Const::CONTROLLER_DIR, '/');
    if ($candidate->hasController()) {
      $classpath .= '_' . ucfirst($candidate->getController());
    } else {
      $classpath .= '_' . ucfirst(Sabel_Const::DEFAULT_CONTROLLER);
    }
    
    if (class_exists($classpath)) {
      $controller = new $classpath();
    } else {
      $controller = new Index_Controllers_Index();
    }
    
    Sabel_Context::setPageController($controller);
    return $controller;
  }
  
  protected function processPostFilter($filters, $controller)
  {
    foreach ($filters as $filter) {
      Sabel::load($filter)->output($controller);
    }
  }
  
  protected function processView($controller)
  {
    if ($controller->hasRendered()) {
      return $controller->getRendered();
    }
    
    $view = new Sabel_View();
    $view->assignByArray($controller->getRequests());
    
    $html = "";
    
    $assigns = array("assign" => $controller->getAssignments());
    
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
    
    $html = $this->processCharset($html);
    
    return $html;
  }
  
  private final function processCharset($html)
  {
    $module = Sabel_Context::getCurrentCandidate()->getModule();
    
    if ($module === "mobile") {
      $header = "Content-Type: text/html; charset=Shift_JIS";
      $html = mb_convert_encoding($html, "SJIS", "UTF-8");
    } else {
      $header = "Content-Type: text/html; charset=UTF-8";
    }
    
    if (!headers_sent()) {
      header($header);
    }
    
    return $html;
  }
}
