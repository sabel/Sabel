<?php

/**
 * Test_Request
 * 
 * @package org.sabel.Test
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Request extends PHPUnit2_Framework_TestCase
{
  protected $map = array();
  
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Request");
  }
  
  public function __construct()
  {
    $this->map = array('uri'=>':module/:controller/:action:/:id',
                       'destination'=>array('module'    => 'test',
                                            'controller'=> 'test',
                                            'action'    => 'test'));
  }
  
  public function setUp()
  {
  }

  public function tearDown()
  {
  }

  public function testRequestBasicUse()
  {
    $entry = new Sabel_Controller_Map_Entry('dummy', $this->map, '/blog/archive/view/1');
    $r = new Sabel_Request_Request($entry, '/blog/archive/view/1');
        
    $this->assertEquals('blog',    $r->getUri()->getModule());
    $this->assertEquals('archive', $r->getUri()->getController());
    $this->assertEquals('view',    $r->getUri()->getAction());
    $this->assertEquals('1',       $r->getUri()->getByName('id'));

    $this->assertFalse($r->hasParameters());
    
    $this->assertNull($r->getParameters()->get('id'));
    $this->assertNull($r->getParameters()->id);
  }
  
  public function testInvalidUri()
  {
    $entry = new Sabel_Controller_Map_Entry('dummy', $this->map, '/blog/archive/view/1');
    $r = new Sabel_Request_Request($entry, '?id=1');
    
    $this->assertEquals('', $r->getUri()->getModule(), 'module is not null');
    $this->assertNull($r->getUri()->getController());
    $this->assertNull($r->getUri()->getAction());
    $this->assertNull($r->getUri()->getByName('id'));
    $this->assertTrue($r->hasParameters());
    $this->assertEquals('1', $r->getParameters()->get('id'));
    $this->assertEquals('1', $r->getParameters()->id);
  }
  
  public function testInvalidModule()
  {
    $entry = new Sabel_Controller_Map_Entry('dummy', $this->map, '/blog/archive/view/1');
    $r = new Sabel_Request_Request($entry, 'id=1/id=1/id=1/1');
    $this->assertEquals('id=1', $r->getUri()->getModule());
  }
  
  public function testRequestWithParameters()
  {
    $r = new Sabel_Request_Request('/blog/archive/view/?id=10');
  }
  
  public function testRequestInvalid()
  {
    $r = new Sabel_Request_Request('/blog/archi?ve/view/?id=10');
  }
}
