<?php

require_once('PHPUnit2/Framework/TestCase.php');

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
    $customer = new Customers();
    $customer->incrementQuantityOfOrder();
  }
  
  public function after($method, $result)
  {
  }
}

class Customers
{
  public function cancelOrder()
  {
  }
  
  public function incrementQuantityOfOrder()
  {
    return "do increment";
  }
}

class Orders
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
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Aspect");
  }
  
  /**
   * @todo think assertion of aspect
   * @todo implementation.
   */
  public function testAspectOriented()
  {
    // $order = Sabel_Container_DI::create()->loadInjected('Orders');
    // $order->registOrder();
  }
}