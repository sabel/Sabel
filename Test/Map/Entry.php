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
  
  // private $map;
  
  public function setUp()
  {
    /*
    $conf = new Sabel_Config_Yaml('Test/data/map.yml');
    
    $this->map = Sabel_Map_Facade::create($conf->toArray());
    $this->map->setRequestUri(new SabeL_Request_Request());
    */
  }
  
  public function tearDown()
  {
    // unset($this->map);
  }
  
  public function testMapEntry()
  {
    $entry = new Sabel_Map_Entry('blog');
    $entry->setUri(':year/:month/:day');
    
    $this->assertTrue(is_object($entry->getUri()));
    $this->assertEquals(':year/:month/:day', $entry->getUri()->getString());
  }
  
  public function testSameNumberOfParts()
  {
    $facade = $this->createFacade();
    
    $this->assertEquals(2, $facade->hasSameUriCountOfEntries(2));
    
    $entries = $facade->getEntriesByCount(2);
    $this->assertEquals('news/:article_id', $entries[0]->getUri()->getString());
    $this->assertEquals('news/:author',     $entries[1]->getUri()->getString());
    
    $requirements = $entries[0]->getRequirements();
    $this->assertTrue($requirements->getByName('article_id')->isMatch(12345678));
    
    $requirements = $entries[1]->getRequirements();
    $this->assertTrue($requirements->getByName('author')->isMatch('tester'));
  }
  
  public function testGetHasConstantUriElement()
  {
    $facade = $this->createFacade();
    
    $entry = $facade->getEntryByHasConstantUriElement(2);
    $this->assertEquals('news', $entry->getUri()->getElement(0)->getConstant());
  }
  
  public function testEntriesIteration()
  {
    $facade = $this->createFacade();
    
    foreach ($facade as $entry) {
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
    
    $map = new Sabel_Map_Facade($conf->toArray());
    $request = new SabeL_Request_Request(null, '/2006/05/02');
    
    $this->assertEquals('2006', $request->getUri()->get(0));
    $map->setRequestUri($request);
    
    // $entry = $map->find();
    // @todo test pass here
    // $this->assertEquals('blog', $entry->getName());
  }
  
  protected function createFacade()
  {
    $facade = new Sabel_Map_Facade();
    $facade->setRequestUri(new SabeL_Request_Request());
    
    $newsEntry = new Sabel_Map_Entry('news');
    $newsAuthorEntry = new Sabel_Map_Entry('newsAuthor');
    
    $newsEntry->setUri(new Sabel_Map_Uri('news/:article_id'));
    $newsAuthorEntry->setUri(new Sabel_Map_Uri('news/:author'));
    
    $newsEntry->setRequirement('article_id', '([0-9]{8})');
    $newsAuthorEntry->setRequirement('author', '([a-zA-Z])');
    
    $facade->setEntry('news', $newsEntry);
    $facade->setEntry('newsAuthor', $newsAuthorEntry);
    
    return $facade;
  }
}