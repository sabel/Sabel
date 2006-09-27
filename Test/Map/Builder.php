<?php

class Test_Map_Builder extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Builder");
  }
  
  public function setUp()
  {
    
  }
  
  public function tearDown()
  {
    
  }
  
  public function testBuild()
  {
    $b = new Sabel_Map_Builder();
    $facade = $b->build('Test/data/map.yml');
    
    $news = $facade->getEntry('news');
    $this->assertEquals('blog', $facade->getEntry('blog')->getName());
    $this->assertEquals('news', $news->getName());
    
    $this->assertTrue($news->getRequirements()->getByName('article_id')->isMatch(12345678));
  }
}