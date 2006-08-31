<?php

class Test_Parameters extends SabelTestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Parameters");
  }
  
  public function testParameters()
  {
    $p = new Sabel_Request_Parameters('?value=test&value2=test2');
    $this->assertTrue(is_object($p));
    $this->assertEquals('test',  $p->value);
    $this->assertEquals('test2', $p->value2);
  }
  
  public function testReal()
  {
    $p = new Sabel_Request_Parameters('?sender=48840&recipient=52971');
    $this->assertEquals('48840', $p->sender);
    $this->assertEquals('52971', $p->recipient);
  }
  
  public function testInvalidParameters()
  {
    $p = new Sabel_Request_Parameters('?test=');
    $this->assertEquals('', $p->test);
  }
  
  public function testNonParameters()
  {
    try {
      $p = new Sabel_Request_Parameters('');
      $this->assertTrue(is_object($p));
      $excepted = true;
    } catch (Exception $e) {
      $excepted = false;
    }
    
    if (!$excepted) $this->fail();
  }

  public function testError()
  {
    try {
      $p = new Sabel_Request_Parameters('/testvalue&=value');
    } catch(Exception $e) {
      
    }
  }
}