<?php

/**
 * Test_Map
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Map extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map");
  }
  
  private $map;
  
  public function setUp()
  {
    $conf = new Sabel_Config_Yaml('Test/data/map.yml');
    
    $this->map = Sabel_Controller_Map::create($conf->toArray());
    $this->map->setRequestUri(new SabeL_Request_Request());
  }
  
  public function tearDown()
  {
    unset($this->map);
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
  
  public function testMapFind()
  {
    $conf = new Sabel_Config_Yaml('Test/data/map.yml');
    
    $map = Sabel_Controller_Map::create($conf->toArray());
    $request = new SabeL_Request_Request(null, '/2006/05/02');
    
    $this->assertEquals('2006', $request->getUri()->get(0));
    $map->setRequestUri($request);
    
    $entry = $map->find();
    // @todo test pass here
    // $this->assertEquals('blog', $entry->getName());
  }
  
  public function testControllerMapDestination()
  {
    $dest = new Sabel_Controller_Map_Destination();
    
    $dest->setModule('news');
    $dest->setController('viewer');
    $dest->setAction('showByDate');
    
    $except = array('module'     => 'news',
                    'controller' => 'viewer',
                    'action'     => 'showByDate');
                    
    $this->assertEquals($except, $dest->toArray());
  }
}