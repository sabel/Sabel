<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

Sabel_Core_Context::addIncludePath('');
uses('sabel.container.DI');
uses('sabel.injection.Calls');
uses('sabel.core.Exception');

class RecordRunningTimeInjection
{
  private $start;
  private $end;
  
  public function when($method)
  {
    return true;
  }
  
  public function before($method, $arg)
  {
    $this->start = microtime();
  }
  
  public function after($method, &$result)
  {
    $this->end = microtime();
  }
  
  public function calcurate()
  {
    return ($this->end - $this->start);
  }
}

class MockInjection
{
  public function when($method)
  {
    return true;
  }
  
  public function after($method, &$result)
  {
    if ($method == 'test') {
      $result = 'mocked!';
    } else if ($method == 'returnArray') {
      $result = array(0 => 'mocked!');
    }
  }
}

class AspectOrderRegistration
{
  public function when($method)
  {
    return ($method == 'registOrder');
  }
  
  public function throws()
  {
  }
  
  public function before($method, $arg)
  {
    $customer = new Customer();
    $customer->incrementQuantityOfOrder();
  }
  
  public function after($method, $result)
  {
  }
}

class Customer
{
  public function cancelOrder()
  {
  }
  
  public function incrementQuantityOfOrder()
  {
    return "do increment";
  }
}

class Order
{
  public function registOrder()
  {
    return "do regist order";
  }
}

/**
 * test case for Sabel LW DI Container
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Aspect extends PHPUnit2_Framework_TestCase
{
  /**
   * @todo think assertion of aspect
   *
   */
  public function testAspectOriented()
  {
    $order = Sabel_Container_DI::create()->loadInjected('Order');
    $order->registOrder();
  }
}