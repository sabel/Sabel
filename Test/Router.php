<?php

$absolute_path = dirname(realpath(__FILE__));
if (!defined('RUN_BASE')) {
  define('RUN_BASE', $absolute_path);
}

require_once('sabel/controller/Map.php');
require_once('sabel/controller/map/Entry.php');
require_once('sabel/controller/map/Uri.php');
require_once('sabel/controller/map/Destination.php');

require_once('sabel/request/Uri.php');

require_once('sabel/core/Router.php');

require_once('sabel/config/Spyc.php');
require_once('sabel/config/Yaml.php');

/**
 * Test_Router
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Router extends SabelTestCase
{
  public static function suite()
  {
   return new PHPUnit2_Framework_TestSuite("Test_Router");
  }
  
  protected $r;
  
  public function setUp()
  {
    $this->r = new Sabel_Core_Router(new Sabel_Controller_Map('/data/map.yml'));
  }
  
  public function tearDown()
  {
    unset($this->r);
  }

  /*
  public function testRouter()
  {
    $dest = $this->r->routing(new Sabel_Request_Uri('2005/06/06'));
    $this->assertEquals('blog', $dest->getModule());
    $this->assertEquals('common', $dest->getController());
  }
  
  public function testEmptyEndOfUri()
  {
    $dest = $this->r->routing(new Sabel_Request_Uri('2005/'));
    $this->assertEquals('blog', $dest->getModule());
    $this->assertEquals('common', $dest->getController());
  }
  */
  
  public function testSingleEntry()
  {
  }
  
  /*
  public function testSameUriCountEntry()
  {
    $dest = $this->r->routing(new Sabel_Request_Uri('news/1'));
    $this->assertEquals('news',   $dest->getModule());
    $this->assertEquals('viewer', $dest->getController());
    $this->assertEquals('viewer', $dest->getController());
  }
  */
}