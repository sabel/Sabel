<?php

require_once('PHPUnit2/Framework/TestCase.php');

require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

uses('sabel.exception.Runtime');
uses('sabel.core.Router');

/**
 * Test_Router
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Router extends PHPUnit2_Framework_TestCase
{
  public function testExample()
  {
    $router = new Sabel_Core_Router();
    $router->routing('');
  }
  
  public function testUri()
  {
    $uri = 'blog/2006/06/04';
    $map = ':controller/:year/:day/:month';
    $pat = '%([a-z].*)/(19|20\d\d)/([01]?\d)/([0-3]?\d)%';
    preg_match($pat, $uri, $matchs);
    array_shift($matchs);
    
    $data = array();
    $maps = split('/', $map);
    foreach ($maps as $pos => $mapPart) {
      $data[ltrim($mapPart, ':')] = $matchs[$pos];
    }
    print_r($data);
  }
}

?>