<?php

/**
 * Test_RequestUri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_RequestUri extends SabelTestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_RequestUri");
  }
  
  private $map   = array();
  private $uri   = null;
  private $entry = null;
  
  public function __construct()
  {
    $this->map = array('uri'=>':module/:controller/:action:/:id',
                       'destination'=>array('module'    => 'test',
                                            'controller'=> 'test',
                                            'action'    => 'test'));
  }
  
  public function setUp()
  {
    $request = new Sabel_Request_Request(null, '/blog/archive/view/1');
    $entry = new Sabel_Map_Entry('dummy', $this->map);
    $entry->setRequest($request);
    $this->uri = new Sabel_Request_Uri('blog/archive/view/1', $entry);
  }
  
  public function tearDown()
  {
  }
  
  public function testCount()
  {
    $this->assertEquals(4, $this->uri->count());
  }
  
  public function testOverrideGetInteger()
  {
    $this->assertEquals(1, $this->uri->id);
  }
  
  public function testOverrideGetString()
  {
    $entry = new Sabel_Map_Entry('dummy', $this->map, '/blog/archive/view/test');
    $this->uri = new Sabel_Request_Uri('blog/archive/view/test', $entry);
    $this->assertEquals('test', $this->uri->id);
  }
  
  public function testGetModule()
  {
    $this->assertEquals('blog', $this->uri->getModule());
  }
  
  public function tsetGetController()
  {
    $this->assertEquals('archive', $this->uri->getController());
  }
  
  public function testGetAction()
  {
    $this->assertEquals('view', $this->uri->getAction());
  }
  
  public function testGetTestByName()
  {
    $this->assertEquals(1,   (int) $this->uri->getByName('id'));
    $this->assertEquals('blog',    $this->uri->getByName('module'));
    $this->assertEquals('archive', $this->uri->getByName('controller'));
    $this->assertEquals('view',    $this->uri->getByName('action'));
  }
  
  public function testHas()
  {
    $this->assertTrue($this->uri->has(0));
    $this->assertTrue($this->uri->has(1));
    $this->assertTrue($this->uri->has(2));
    $this->assertTrue($this->uri->has(3));
    $this->assertFalse($this->uri->has(4));
    
    for ($pos = 0; $pos < $this->uri->count(); $pos++) {
      $this->assertTrue($this->uri->has($pos));
      $this->assertTrue(is_string($this->uri->get($pos)));
    }
  }
}