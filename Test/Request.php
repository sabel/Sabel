<?php

Sabel::using('Sabel_Request');

/**
 * Test_Request
 * 
 * @package org.sabel.Test
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Request extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Request");
  }
  
  public function setUp()
  {
  }

  public function tearDown()
  {
  }
  
  public function testGetPostRequests()
  {
    $r = new Sabel_Request_Web();
    $result = $r->getPostRequests(array("test" => "test"));
    $this->assertEquals(array("test"=>"test"), $result);
  }
  
  public function testInvalidModule()
  {
    $r = new Sabel_Request_Web('id=1/id=1/id=1/1');
  }
  
  public function testRequestWithParameters()
  {
    $r = new Sabel_Request_Web('/blog/archive/view/?id=10');
  }
  
  public function testRequestInvalid()
  {
    $r = new Sabel_Request_Web('/blog/archi?ve/view/?id=10');
  }
}
