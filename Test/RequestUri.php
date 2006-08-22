<?php

$absolute_path = dirname(realpath(__FILE__));
if (!defined('RUN_BASE')) {
  define('RUN_BASE', $absolute_path);
}

require_once('PHPUnit2/Framework/TestCase.php');

require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

/*
require_once('sabel/controller/Map.php');
require_once('sabel/controller/map/Entry.php');
require_once('sabel/controller/map/Uri.php');
require_once('sabel/controller/map/Destination.php');
*/

require_once('sabel/env/Server.php');
require_once('sabel/request/Uri.php');
require_once('sabel/request/Parameters.php');

/*
require_once('sabel/core/Router.php');

require_once('sabel/config/Spyc.php');
require_once('sabel/config/Yaml.php');
*/

/**
 * Test_RequestUri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_RequestUri extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_RequestUri");
  }
  
  public function setUp()
  {
  }

  public function tearDown()
  {
  }

  public function testNoParameter()
  {
    
  }

  public function testUseParameter()
  {
    
  }
  
  public function testInvalidUri()
  {
    
  }
}
