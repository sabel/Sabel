<?php

Sabel::using('Sabel_Request');

/**
 * Test_Request
 * 
 * @package org.sabel.Test
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Request extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Request");
  }
  
  public function setUp()
  {
  }

  public function tearDown()
  {
  }

  // public function testRequestBasicUse()
  // {
  //   $r = new Sabel_Request(null, '/blog/archive/view/1');
  //   
  //   $this->assertEquals('blog',    $r->getUri()->getModule());
  //   $this->assertEquals('archive', $r->getUri()->getController());
  //   $this->assertEquals('view',    $r->getUri()->getAction());
  //   $this->assertEquals('1',       $r->getUri()->getByName('id'));
  // 
  //   $this->assertFalse($r->hasParameters());
  // }
  // 
  // public function testInvalidUri()
  // {
  //   $r = new Sabel_Request(null, '?id=1');
  //   
  //   $this->assertEquals('', $r->getUri()->getModule(), 'module is not null');
  //   $this->assertNull($r->getUri()->getController());
  //   $this->assertNull($r->getUri()->getAction());
  //   $this->assertNull($r->getUri()->getByName('id'));
  //   $this->assertEquals('1', $r->getParameters()->get('id'));
  //   $this->assertEquals('1', $r->getParameters()->id);
  // }
  // 
  public function testInvalidModule()
  {
    $r = new Sabel_Request_Web(null, 'id=1/id=1/id=1/1');
  }
  
  public function testRequestWithParameters()
  {
    $r = new Sabel_Request_Web(null, '/blog/archive/view/?id=10');
  }
  
  public function testRequestInvalid()
  {
    $r = new Sabel_Request_Web(null, '/blog/archi?ve/view/?id=10');
  }
}
