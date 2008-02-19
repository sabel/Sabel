<?php

/**
 * test for sabel.response.header.Cli
 * using classes: sabel.response.Object
 *
 * @category Response
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Response_Header extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Response_Header");
  }
  
  public function testOutputHeader()
  {
    $response = new Sabel_Response_Object();
    $response->setHeader("Content-Type",   "text/html; charset=UTF-8");
    $response->setHeader("Content-Length", "4096");
    
    $header = new Sabel_Response_Header_Cli();
    $headers = $header->output($response);
    $this->assertEquals("Content-Type: text/html; charset=UTF-8", $headers[0]);
    $this->assertEquals("Content-Length: 4096", $headers[1]);
  }
  
  public function testOutputStatus()
  {
    $header = new Sabel_Response_Header_Cli();
    
    $response = new Sabel_Response_Object();
    $response->forbidden();
    $headers = $header->output($response);
    $this->assertEquals("HTTP/1.0 403 Forbidden", $headers[0]);
    
    $response = new Sabel_Response_Object();
    $response->notModified();
    $headers = $header->output($response);
    $this->assertEquals("HTTP/1.0 304 Not Modified", $headers[0]);
  }
}
