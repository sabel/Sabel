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
    $facade = $this->createFacadeFromConfig();
    
    $facade->setRequestUri(new Sabel_Request(null, '/2006/05/02'));
    $this->assertEquals('blog', $facade->find()->getName());
  }
  
  public function testMapFindWithConstant()
  {
    $facade = $this->createFacadeFromConfig();
    $facade->setRequestUri(new Sabel_Request(null, '/news/tester'));
    $this->assertEquals('newsAuthor', $facade->find()->getName());
  }
  
  public function testMapFindWithConstantAndRequirement()
  {
    $facade = $this->createFacadeFromConfig();
    $facade->setRequestUri(new Sabel_Request(null, '/news/12341234'));
    $this->assertEquals('news', $facade->find()->getName());
  }
  
  public function testMapFindNotFound()
  {
    $facade = $this->createFacadeFromConfig();
    $request = new Sabel_Request(null, '/index/blog/top/14');
    
    $entry = $facade->find();
    $this->assertEquals('default', $entry->getName());
  }
  
  public function testUriBlog()
  {
    $uriParam = array('year'=>'2005', 'month'=>'08');
    $facade = $this->createFacadeFromConfig();
    $result = $facade->getEntry('blog')->uri($uriParam);
    $this->assertEquals('2005/08', $result);
  }
  
  public function testUriBlogFill()
  {
    $uriParam = array('year'=>'2005', 'month'=>'08', 'day'=>'12');
    $facade = $this->createFacadeFromConfig();
    $result = $facade->getEntry('blog')->uri($uriParam);
    $this->assertEquals('2005/08/12', $result);
  }
  
  public function testUriDefault()
  {
    $uriParam = array('module'=>'index', 'controller'=>'shop', 'action'=>'test');
  }
  
  public function testUriWithConstant()
  {
    $facade = $this->createFacadeFromConfig();
    $result = $facade->getEntry('news')->uri(array('article_id'=>'12341235'));
    $this->assertEquals('news/12341235', $result);
  }
  
  public function testUriWithConstantAndRequest()
  {
    $facade = $this->createFacadeFromConfig();
    $facade->setRequestUri(new Sabel_Request(null, 'news/123412345'));
    $result = $facade->getEntry('news')->uri(array('article_id'=>'12341235'));
    $this->assertEquals('news/12341235', $result);
  }
  
  
  public function testUriWithController()
  {
    $facade = $this->createFacadeFromConfig();
    $result = $facade->getEntry('testController')->uri(array('controller'=>'test',
                                                             'id'=>'0123456789'));
                                                             
    $this->assertEquals('test/0123456789', $result);
  }
  
  public function testUriWithControllerUsingRequestValue()
  {
    $facade = $this->createFacadeFromConfig();
    $facade->setRequestUri(new Sabel_Request(null, 'test/0123456789'));
    $entry  = $facade->getEntry('testController');
    $result = $entry->uri(array('id' => '0123456789'));
                                                    
    $this->assertEquals('test/0123456789', $result);
  }
  
  protected function createFacadeFromConfig()
  {
    $b = new Sabel_Map_Builder();
    $b->load('Test/data/map.yml');
    $facade = $b->build();
    return $facade;
  }
  
  protected function createFacade()
  {
    $facade = new Sabel_Map_Facade();
    $facade->setRequestUri(new Sabel_Request());
    
    $newsEntry = new Sabel_Map_Entry('news');
    $newsAuthorEntry = new Sabel_Map_Entry('newsAuthor');
    
    $newsEntry->setUri(new Sabel_Map_Uri('news/:article_id'));
    $newsAuthorEntry->setUri(new Sabel_Map_Uri('news/:author'));
    
    $newsEntry->setRequirement('article_id', '([0-9]{8})');
    $newsAuthorEntry->setRequirement('author', '([a-zA-Z])');
    
    $destNews = new Sabel_Map_Destination();
    $destNews->setModule('news');
    $destNews->setController('viewer');
    $destNews->setAction('show');
    $newsEntry->setDestination($destNews);
    
    $destAuthor = new Sabel_Map_Destination();
    $destAuthor->setModule('news');
    $destAuthor->setController('viewer');
    $destAuthor->setAction('showByAuthor');
    $newsAuthorEntry->setDestination($destAuthor);
    
    $facade->setEntry('news', $newsEntry);
    $facade->setEntry('newsAuthor', $newsAuthorEntry);
    
    return $facade;
  }
}
