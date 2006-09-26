<?php

/**
 * Test_Map
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Map_Destination extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Destination");
  }
  
  public function setUp()
  {
  }
  
  public function tearDown()
  {
  }
  
  public function testGetArray()
  {
    $dest = new Sabel_Map_Destination();
    
    $dest->setModule('news');
    $dest->setController('viewer');
    $dest->setAction('showByDate');
    
    $except = array('module'     => 'news',
                    'controller' => 'viewer',
                    'action'     => 'showByDate');
                    
    $this->assertEquals($except, $dest->toArray());
  }
  
  public function testGetSpecificValue()
  {
    $dest = new Sabel_Map_Destination();
    
    $dest->setModule('blog');
    $dest->setController('common');
    $dest->setAction('showByDate');
    
    $this->assertTrue($dest->hasModule());
    $this->assertEquals('blog', $dest->getModule());
    
    $this->assertTrue($dest->hasController());
    $this->assertEquals('common', $dest->getController());
    
    $this->assertTrue($dest->hasAction());
    $this->assertEquals('showByDate', $dest->getAction());
  }
}