<?php

/**
 * TestCase of sabel.aspect.*
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Aspect extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Aspect");
  }
  
  public function testDynamicProxy()
  {
    $before = create_function('$target, $method', 'return $method->getName();');
    $after  = create_function('$target, $method, $result', 'return $result;');
    $both   = create_function('$target, $method, $result', 'return null;');
    
    $customer = new Sabel_Aspect_DynamicProxy(new Test_Aspect_Customers());
    $customer->beforeAspect('before', $before);
    $customer->afterAspect('after', $after);
    $customer->bothAspect('both', $both);
    $customer->getOrder();
    
    $this->assertEquals($customer->beforeResult('before'), 'getOrder');
    $this->assertEquals($customer->afterResult('after'), 'order');
    $this->assertEquals($customer->bothResult('both'), null);
  }
  
  public function testNestedOverloads()
  {
    $arg     = '$target, $method, $result';
    $routine = 'return $result;';
    
    $ol = new Sabel_Aspect_DynamicProxy(new Test_Aspect_Overloads());
    $ol->afterAspect('after', create_function($arg, $routine));
    $ol->callOverloads('test');
    $this->assertEquals('callOverloads', $ol->afterResult('after'));
    
    $ol->callOverloadsTwo('test');
    $this->assertEquals('callOverloadsTwo', $ol->afterResult('after'));
  }
}

class Test_Aspect_Customers
{
  public function getOrder()
  {
    return 'order';
  }
}

class Test_Aspect_Overloads
{
  public function __call($method, $arg)
  {
    return $method;
  }
}