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
uses('sabel.request.Parameters');
uses('sabel.request.ParsedRequest');

class Test_Sabel extends PHPUnit2_Framework_TestCase
{
  public function testParameters()
  {
    $parameters = new Parameters('&value?value=test&value2=test2');
    $this->assertTrue(is_object($parameters));
    $this->assertEquals('test', $parameters->value);
    $this->assertEquals('test2', $parameters->value2);
  }
  
  public function testParametersReal()
  {
    $parameters = new Parameters('?sender=48840&recipient=52971');
    $this->assertEquals('48840', $parameters->sender);
    $this->assertEquals('52971', $parameters->recipient);
  }
  
  public function testInvalidParameters()
  {
    $parameters = new Parameters('?test=');
    $this->assertEquals('', $parameters->test);
  }
  
  public function testNonParameters()
  {
    try {
      $parameters = new Parameters('');
      $this->assertTrue(is_object($parameters));
      $excepted = true;
    } catch (Exception $e) {
      $excepted = false;
    }
    
    if (!$excepted) $this->fail();
  }
  
  public function testParametersError()
  {
    try {
      $parameters = new Parameters('/testvalue&=value');
    } catch(Exception $e) {
      
    }
  }
}