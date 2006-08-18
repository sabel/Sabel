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
require_once('sabel/controller/map/Requirements.php');
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
  public function __construct()
  {
    $this->map = new Sabel_Controller_Map('/data/map.yml');
    $this->map->load();
  }
  
  public function testMapUri()
  {
    $entry  = $this->map->getEntry('blog');
    $mapUri = $entry->getUri();
    $this->assertFalse($mapUri->getElement(-1));
    $this->assertEquals(':year',  $mapUri->getElement(0)->toString());
    $this->assertEquals(':month', $mapUri->getElement(1)->toString());
    $this->assertEquals(':day',   $mapUri->getElement(2)->toString());
    $this->assertFalse($mapUri->getElement(3));
    
    foreach ($mapUri->getElements() as $element) {
      $this->assertTrue(is_object($element));
    }
  }
  
  public function testMapElement()
  {
    $entry = $this->map->getEntry('default');
    $this->assertTrue($entry->getUri()->getElement(0)->isModule());
    $this->assertTrue($entry->getUri()->getElement(1)->isController());
    $this->assertTrue($entry->getUri()->getElement(2)->isAction());
    
    $this->assertTrue($entry->getUri()->getElement(0)->isReservedWord());
    $this->assertTrue($entry->getUri()->getElement(1)->isReservedWord());
    $this->assertTrue($entry->getUri()->getElement(2)->isReservedWord());
  }
  
  public function testMapElementConst()
  {
    $entry = $this->map->getEntry('news');
    $this->assertTrue($entry->getUri()->getElement(0)->isConstant());
    $this->assertFalse($entry->getUri()->getElement(1)->isConstant());
  }
  
  public function testMapEntry()
  {
    $entry = $this->map->getEntry('blog');
    $this->assertTrue(is_object($entry->getUri()));
    $this->assertEquals(':year/:month/:day', $entry->getUri()->getString());
  }
  
  public function testSameNumberOfParts()
  {
    $entries = $this->map->getEntriesByCount(2);
    $this->assertEquals('news/:author',     $entries[0]->getUri()->getString());
    $this->assertEquals('news/:article_id', $entries[1]->getUri()->getString());
    
    $requirements = $entries[0]->getRequirements();
    $this->assertTrue($requirements[0]->isMatch('tester'));
    
    $requirements = $entries[1]->getRequirements();
    $this->assertTrue($requirements[0]->isMatch(12345670));
  }
  
  public function testHasSameNumberOfUriParts()
  {
    $this->assertTrue(!$this->map->hasSameUriCountOfEntries(2) === false);
  }
  
  public function testGetHasConstantUriElement()
  {
    $entry = $this->map->getEntryByHasConstantUriElement(2);
    $this->assertEquals('news', $entry->getUri()->getElement(0)->getConstant());
  }
  
  public function testMapEntries()
  {
    foreach ($this->map as $entry) {
      $this->assertTrue(is_object($entry->getUri()));
      $uri = $entry->getUri();
      $this->assertTrue(is_string($uri->getString()));
      foreach ($uri->getElements() as $element) {
        $this->assertTrue(is_object($element));
      }
    }
  }
  
  public function testMapDestination()
  {
    $entry = $this->map->getEntry('blog');
    $dest = $entry->getDestination();
    $this->assertTrue($dest->hasModule());
    $this->assertEquals('blog', $dest->getModule());
    
    $this->assertTrue($dest->hasController());
    $this->assertEquals('common', $dest->getController());
    
    $this->assertTrue($dest->hasAction());
    $this->assertEquals('showByDate', $dest->getAction());
  }
}
