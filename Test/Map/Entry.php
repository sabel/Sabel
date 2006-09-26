<?php

/**
 * Test_Map
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Map_Entry extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Entry");
  }
  
  private $map;
  
  public function setUp()
  {
    $conf = new Sabel_Config_Yaml('Test/data/map.yml');
    
    $this->map = Sabel_Map_Facade::create($conf->toArray());
    $this->map->setRequestUri(new SabeL_Request_Request());
  }
  
  public function tearDown()
  {
    unset($this->map);
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
  
  public function testEntriesIteration()
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
  
  public function testMapFind()
  {
    $conf = new Sabel_Config_Yaml('Test/data/map.yml');
    
    $map = Sabel_Map_Facade::create($conf->toArray());
    $request = new SabeL_Request_Request(null, '/2006/05/02');
    
    $this->assertEquals('2006', $request->getUri()->get(0));
    $map->setRequestUri($request);
    
    $entry = $map->find();
    // @todo test pass here
    // $this->assertEquals('blog', $entry->getName());
  }
}