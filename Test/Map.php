<?php

$absolute_path = dirname(realpath(__FILE__));
define('RUN_BASE', $absolute_path);

require_once('PHPUnit2/Framework/TestCase.php');

require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/controller/Map.php');
require_once('sabel/controller/map/Entry.php');
require_once('sabel/config/Spyc.php');
require_once('sabel/config/Yaml.php');

/**
 * Test_Map
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Map extends PHPUnit2_Framework_TestCase
{
  public function setUp()
  {
    $this->map = new Sabel_Controller_Map('/data/map.yml');
    $this->map->load();
  }
  
  public function testMapEntry()
  {
    $entry = $this->map->getEntry('blog');
    $this->assertTrue(is_string($entry->getUri()));
    $this->assertEquals(':year/:month/:day', $entry->getUri());
  }
  
  public function testMapEntries()
  {
    foreach ($this->map->getEntries() as $entry) {
      $this->assertTrue(is_string($entry->getUri()));
    }
  }
}

?>