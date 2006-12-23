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
  
  public function ignition($requestUri = null)
  {
    $request   = $this->processRequest($requestUri);
    $candidate = $this->processCandidate($request->__toString());
    $filters   = $this->loadFilters($candidate);
    $this->processHelper($this->desideHelperPath($request, $candidate));
    $this->processPreFilter($filters, $request);
    $controller = $this->processPageController($candidate);
    
    $controller->setup($request, Sabel::load('Sabel_View')->decideTemplatePath($candidate));
    $controller->initialize();
    
    $responses = $controller->execute($candidate->getAction());
    
    $this->processPostFilter($filters, $controller, $responses);
    
    return array('html' => $controller->rendering(), 'responses' => $responses);
  }
  
  protected function processRequest($requestUri)
  {
    if (is_object($requestUri)) {
      $request = $requestUri;
    } elseif (is_string($requestUri)) {
      $request = Sabel::load($this->requestClass, $requestUri);
    } else {
      $request = Sabel::load($this->requestClass);
    }
    
    return $request;
  }
  
  protected function processCandidate($requestString)
  {
    if (ENVIRONMENT !== DEVELOPMENT) {
      $cache = Sabel::load('Sabel_Cache_Apc');
      if (!($candidate = $cache->read($requestString))) {
        $candidate = Sabel::load('Sabel_Map_Candidate');
        $candidate = $candidate->find(Sabel::load('Sabel_Map_Tokens', $requestString));
        $cache->write($requestString, $candidate);
      }
    } else {
      $candidate = Sabel::load('Sabel_Map_Candidate');
      $candidate = $candidate->find(Sabel::load('Sabel_Map_Tokens', $requestString));
    }
    
    Sabel_Context::setCurrentCandidate($candidate);
    
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
  
  protected function processHelper($path)
  {
    if (is_file($path)) Sabel::fileUsing($path);
  }
  
  protected function desideHelperPath($request, $candidate)
  {
    $module = $candidate->getModule();
    $cntr   = $candidate->getController();
    $action = $candidate->getAction();
    return RUN_BASE . "/app/{$module}/helpers/${cntr}.${action}.php";
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
