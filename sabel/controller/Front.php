<?php

Sabel::using('Sabel_View');
Sabel::using('Sabel_Context');
Sabel::using('Sabel_Map_Candidate');
Sabel::using('Sabel_Exception_Runtime');

/**
 * Sabel_Controller_Front
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Front
{
  protected $requestClass = "Sabel_Request_Web";
  
  public function __construct()
  {
    Sabel::fileUsing(RUN_BASE . '/config/map.php');
  }
  
  public function ignition(Sabel_Request $request = null)
  {
    if ($request === null) $request = Sabel::load($this->requestClass);
    $candidate = $this->processCandidate($request);
    $filters   = $this->loadFilters($candidate);
    $this->processHelper($request, $candidate);
    $this->processPreFilter($filters, $request);
    $controller = $this->processPageController($candidate);
    $controller->setup($request, Sabel::load('Sabel_View')->decideTemplatePath($candidate), $candidate->getAction());
    $controller->initialize();
    
    $responses = $controller->execute($candidate->getAction());
    
    $this->processPostFilter($filters, $controller, $responses);
    
    return array('html' => $controller->rendering(), 'responses' => $responses);
  }
  
  protected function processCandidate(Sabel_Request $request)
  {
    if (ENVIRONMENT !== DEVELOPMENT) {
      $cache = Sabel::load('Sabel_Cache_Apc');
      if (!($candidate = $cache->read($request->__toString()))) {
        $candidate = Sabel::load('Sabel_Map_Candidate');
        $candidate = $candidate->find(Sabel::load('Sabel_Map_Tokens', $request->__toString()));
        $cache->write($request->__toString(), $candidate);
      }
    } else {
      $candidate = Sabel::load('Sabel_Map_Candidate');
      $candidate = $candidate->find(Sabel::load('Sabel_Map_Tokens', $request->__toString()));
    }
    
    Sabel_Context::setCurrentCandidate($candidate);
    $request->setCandidate($candidate);
    
    return $candidate;
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
              $filters[] = join("_", array('Filters', str_replace(".php", "", $file)));
            } else {
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
    
    $moduleSpecificHelpersPath     = RUN_BASE . "/app/{$module}/helpers/application.php";
    $controllerSpecificHelpersPath = RUN_BASE . "/app/{$module}/helpers/${cntr}.php";
    $actionSpecificHelpersPath     = RUN_BASE . "/app/{$module}/helpers/${cntr}.${action}.php";
    
    if (is_file($moduleSpecificHelpersPath)) {
      Sabel::fileUsing($moduleSpecificHelpersPath);
    }
    
    if (is_file($controllerSpecificHelpersPath)) {
      Sabel::fileUsing($controllerSpecificHelpersPath);
    }
    
    if (is_file($actionSpecificHelpersPath)) {
      Sabel::fileUsing($actionSpecificHelpersPath);
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
    
    Sabel::using($classpath);
    if (class_exists($classpath)) {
      $controller = new $classpath();
    } else {
      Sabel::using('Index_Controllers_Index');
      $controller = new Index_Controllers_Index();
    }
    
    Sabel_Context::setPageController($controller);
    return $controller;
  }
  
  protected function processPostFilter($filters, $controller, $responses)
  {
    foreach ($filters as $filter) {
      Sabel::load($filter)->output($responses);
    }
  }
}
