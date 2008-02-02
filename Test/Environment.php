<?php

/**
 * testcase for sabel.Environment
 *
 * @category  Core
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Environment extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Environment");
  }
  
  public function testIsWindows()
  {
    $isWin = (DIRECTORY_SEPARATOR === '\\');
    $env = Sabel_Environment::create();
    $env->set("os", null);
    $this->assertEquals($isWin, $env->isWin());
    
    $env->set("os", "WINNT");
    $this->assertTrue($env->isWin());
    
    $env->set("os", null);
  }
  
  public function testHttps()
  {
    $this->assertFalse(Sabel_Environment::create()->isHttps());
    
    $_SERVER["HTTPS"] = "on";
    $this->assertTrue(Sabel_Environment::create()->isHttps());
  }
  
  public function testHttpHost()
  {
    $env = Sabel_Environment::create();
    unset($_SERVER["HTTP_HOST"]);
    $this->assertEquals("localhost", $env->get("HTTP_HOST"));
    
    $_SERVER["HTTP_HOST"] = "www.example.com";
    $this->assertEquals("www.example.com", $env->get("HTTP_HOST"));
  }
  
  public function testServerName()
  {
    $env = Sabel_Environment::create();
    unset($_SERVER["SERVER_NAME"]);
    $this->assertEquals("localhost", $env->get("SERVER_NAME"));
    
    $_SERVER["SERVER_NAME"] = "www.example.com";
    $this->assertEquals("www.example.com", $env->get("SERVER_NAME"));
  }
  
  public function testServerPort()
  {
    $env = Sabel_Environment::create();
    unset($_SERVER["SERVER_PORT"]);
    $this->assertEquals("80", $env->get("SERVER_PORT"));
    
    $_SERVER["SERVER_PORT"] = "8080";
    $this->assertEquals("8080", $env->get("SERVER_PORT"));
  }
}
