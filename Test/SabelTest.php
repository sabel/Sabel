<?php

require_once('PHPUnit2/Framework/TestCase.php');
require_once('core/Parameters.php');

class Test_SabelTest extends PHPUnit2_Framework_TestCase
{
  public function testParameters()
  {
    $parameters = new Parameters('value?value=test&value2=test2');
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
    try {
     $parameters = new Parameters('?test=');
     $excepted = false;
   } catch (Exception $e) {
     $excepted = true;
   }
   
   if (!$excepted) $this->fail();
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