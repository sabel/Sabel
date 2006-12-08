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
    if (is_object($requestUri)) {
      $request = $requestUri;
    } elseif (is_string($requestUri)) {
      $request = Sabel::load('Sabel_Request', $requestUri);
    } else {
      $request = Sabel::load('Sabel_Request');
    }
    
    $cache = Sabel::load('Sabel_Cache_Apc');
    if (!($candidate = $cache->read($request->__toString()))) {
      $candidate = Sabel::load('Sabel_Map_Candidate');
      $candidate = $candidate->find(Sabel::load('Sabel_Map_Tokens', $request->__toString()));
      $cache->write($request->__toString(), $candidate);
    }
    
    Sabel_Context::setCurrentCandidate($candidate);
    
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
    
    $view = new Sabel_View($candidate->getModule(), $candidate->getController(), $candidate->getAction());
    $controller->setup($request, $view);
    $controller->initialize();
    
    $responses = $controller->execute($candidate->getAction());
    return array('html' => $controller->rendering(), 'responses' => $responses);
  }
}