<?php

/**
 * Test_Map_Element
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Map_Uri extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Uri");
  }
  
  public function setUp()
  {
  }
  
  public function tearDown()
  {
  }
  
  public function testUriElement()
  {
    $uri = new Sabel_Map_Uri(':module/:controller/:action/:id');
    
    $this->assertTrue($uri->getElement(0)->isModule());
    $this->assertTrue($uri->getElement(1)->isController());
    $this->assertTrue($uri->getElement(2)->isAction());
                            
    $this->assertTrue($uri->getElement(0)->isReservedWord());
    $this->assertTrue($uri->getElement(1)->isReservedWord());
    $this->assertTrue($uri->getElement(2)->isReservedWord());
  }
  
  public function testUri()
  {
    $mapUri = new Sabel_Map_Uri(':year/:month/:day');
    
    $this->assertFalse($mapUri->getElement(-1));
    $this->assertEquals(':year',  $mapUri->getElement(0)->toString());
    $this->assertEquals(':month', $mapUri->getElement(1)->toString());
    $this->assertEquals(':day',   $mapUri->getElement(2)->toString());
    $this->assertFalse($mapUri->getElement(3));
    
    foreach ($mapUri->getElements() as $element) {
      $this->assertTrue(is_object($element));
    }
  }
  
  public function testConstantElement()
  {
    $uri = new Sabel_Map_Uri('news/:article_id');
    $this->assertTrue($uri->getElement(0)->isConstant());
    $this->assertFalse($uri->getElement(1)->isConstant());
  }
}