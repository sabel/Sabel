<?php

$absolute_path = dirname(realpath(__FILE__));
define('RUN_BASE', $absolute_path);

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
  public function setUp()
  {
  }

  public function tearDown()
  {
  }

  public function testNoParameter()
  {
    $ru = new Sabel_Request_Uri(10);
    $this->assertTrue($ru->has(0));
    $this->assertEquals('10', $ru->get(0));
    $this->assertFalse($ru->has(1));
    $this->assertNull($ru->get(1));

    $this->assertEquals(1, $ru->count());

    $this->assertFalse($ru->hasParameters());
    $this->assertNull($ru->getParameters());
  }

  public function testUseParameter()
  {
    $ru = new Sabel_Request_Uri('/blog/archive/view/?id=10');
  }
  
  public function testInvalidUri()
  {
    $ru = new Sabel_Request_Uri('/blog/archive/view/');
    var_dump($ru);
    $this->assertFalse($ru->hasP);
  }
}
