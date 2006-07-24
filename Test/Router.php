<?php

$absolute_path = dirname(realpath(__FILE__));
define('RUN_BASE', $absolute_path);

require_once('PHPUnit2/Framework/TestCase.php');

require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/controller/Map.php');
require_once('sabel/controller/map/Entry.php');
require_once('sabel/controller/map/Uri.php');
require_once('sabel/controller/map/Destination.php');

require_once('sabel/request/URI.php');

require_once('sabel/core/Router.php');

require_once('sabel/config/Spyc.php');
require_once('sabel/config/Yaml.php');

/**
 * Test_Router
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Router extends PHPUnit2_Framework_TestCase
{
  public function testUri()
  {
    $uri = '2006/06/04';
    $map = ':year/:day/:month';
    $pat = '%(19|20\d\d)/([01]?\d)/([0-3]?\d)%';
    preg_match($pat, $uri, $matchs);
    array_shift($matchs);
    
    $data = array();
    $maps = split('/', $map);
    foreach ($maps as $pos => $mapPart) {
      $data[ltrim($mapPart, ':')] = $matchs[$pos];
    }
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
  
  public function testSingleEntry()
  {
    $dest = $this->r->routing(new Sabel_Request_Uri('10'));
    $this->assertEquals('entry', $dest->getAction());
  }
  
  public function testSameUriCountEntry()
  {
    $dest = $this->r->routing(new Sabel_Request_Uri('news/1'));
    $this->assertEquals('news',   $dest->getModule());
    $this->assertEquals('viewer', $dest->getController());
    $this->assertEquals('viewer', $dest->getController());
  }
}