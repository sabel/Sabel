<?php

require_once('PHPUnit2/Framework/TestCase.php');
require_once('core/Parameters.php');

class Test_SabelTest extends PHPUnit2_Framework_TestCase
{
  public function testParameters()
  {
    $parameters = new Parameters('/test?value=test&value2=test2');
    $this->assertTrue(is_object($parameters));
    $this->assertEquals('test', $parameters->value);
    $this->assertEquals('test2', $parameters->value2);
  }
  
  public function testParametersError()
  {
    try {
      $parameters = new Parameters('/testvalue&=value');
      $this->fail();
    } catch(Exception $e) {
      // ok
    }
  }
}