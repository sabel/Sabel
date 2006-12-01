<?php

/**
 * Test_RequestUri
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Http extends SabelTestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_RequestUri");
  }
  
  public function __construct()
  {
  }
  
  public function setUp()
  {
    
  }
  
  public function tearDown()
  {
  }
  
  public function testRequest()
  {
    $r = new Sabel_Http_Request();
    $response = $r->request('localhost', '/index.html', '', 'get');
  }
}