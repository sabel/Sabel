<?php

Sabel::using('Sabel_View');
Sabel::using('Sabel_Context');
Sabel::using('Sabel_Map_Candidate');
Sabel::using('Sabel_Exception_Runtime');
Sabel::fileUsing('sabel/db/Functions.php');

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
  public function __construct()
  {
    Sabel::fileUsing(RUN_BASE . '/config/map.php');
  }
  
  public function ignition($requestUri = null)
  {
    $request    = $this->processRequest($requestUri);
    $candidate  = $this->processCandidate($request->__toString());
    $filters    = $this->processPreFilter($candidate, $request);
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
      $request = Sabel::load('Sabel_Request', $requestUri);
    } else {
      $request = Sabel::load('Sabel_Request');
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
        $cache->write($request->__toString(), $candidate);
      }
    } else {
      $candidate = Sabel::load('Sabel_Map_Candidate');
      $candidate = $candidate->find(Sabel::load('Sabel_Map_Tokens', $requestString));
    }
    
    Sabel_Context::setCurrentCandidate($candidate);
    
    return $candidate;
  }
  
  protected function processPreFilter($candidate, $request)
  {
    $filtersDir = RUN_BASE . "/app/{$candidate->getModule()}/filters";
    $filters = array();
    if (is_dir($filtersDir)) {
      if ($dh = opendir($filtersDir)) {
        while (($file = readdir($dh)) !== false) {
          if ($file{0} !== ".") {
            $filters[] = join('_', array(ucfirst($candidate->getModule()),
                                   ucfirst('filters'),
                                   str_replace(".php", "", $file)));
          }
        }
        closedir($dh);
      }
    }
    
    foreach ($filters as $filter) {
      Sabel::load($filter)->setup($request)->execute();
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
    $controller = new $classpath();
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
