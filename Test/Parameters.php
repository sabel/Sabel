<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

Sabel_Core_Context::addIncludePath('');
uses('sabel.container.DI');
uses('sabel.injection.Calls');
uses('sabel.core.Exception');
uses('sabel.core.Const');
uses('sabel.request.Sabel_Request_Parameters');

class Test_Parameters extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Parameters");
  }
  
  public function testSabel_Request_Parameters()
  {
    $Sabel_Request_Parameters = new Sabel_Request_Parameters('&value?value=test&value2=test2');
    $this->assertTrue(is_object($Sabel_Request_Parameters));
    $this->assertEquals('test', $Sabel_Request_Parameters->value);
    $this->assertEquals('test2', $Sabel_Request_Parameters->value2);
  }
  
  public function testSabel_Request_ParametersReal()
  {
    $Sabel_Request_Parameters = new Sabel_Request_Parameters('?sender=48840&recipient=52971');
    $this->assertEquals('48840', $Sabel_Request_Parameters->sender);
    $this->assertEquals('52971', $Sabel_Request_Parameters->recipient);
  }
  
  public function testInvalidSabel_Request_Parameters()
  {
    $Sabel_Request_Parameters = new Sabel_Request_Parameters('?test=');
    $this->assertEquals('', $Sabel_Request_Parameters->test);
  }
  
  public function testNonSabel_Request_Parameters()
  {
    try {
      $Sabel_Request_Parameters = new Sabel_Request_Parameters('');
      $this->assertTrue(is_object($Sabel_Request_Parameters));
      $excepted = true;
    } catch (Exception $e) {
      $excepted = false;
    }
    
    if (!$excepted) $this->fail();
  }
  
  public function testSabel_Request_ParametersError()
  {
    try {
      $Sabel_Request_Parameters = new Sabel_Request_Parameters('/testvalue&=value');
    } catch(Exception $e) {
      
    }
  }
}