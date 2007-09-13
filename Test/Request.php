<?php

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
  
  public function testEmptyUri()
  {
    $request = new Sabel_Request_Object();
    $uri = $request->getUri();
    $this->assertEquals("", $uri->__toString());
  }
  
  public function testNormalUri()
  {
    $request = new Sabel_Request_Object("index/index");
    $uri = $request->getUri();
    $this->assertEquals("index/index", $uri->__toString());
  }
  
  public function testValuesDefault()
  {
    $request = new Sabel_Request_Object();
    $values = array("test" => "test");
    $request->values($values);
    $this->assertEquals($values, $request->fetchGetValues());
  }
  
  public function testValuesGet()
  {
    $request = new Sabel_Request_Object();
    $request->method("GET");
    $values = array("test" => "test");
    $request->values($values);
    $this->assertEquals($values, $request->fetchGetValues());
  }
  
  public function testValuesPost()
  {
    $request = new Sabel_Request_Object();
    $request->method("POST");
    $values = array("test" => "test");
    $request->values($values);
    $this->assertNotEquals($values, $request->fetchGetValues());
  }
}
