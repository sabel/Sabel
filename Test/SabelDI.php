<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/container/DI.php');
require_once('sabel/core/spyc.php');

Sabel_Core_Context::addIncludePath('');
uses('sabel.container.ReflectionClass');
uses('sabel.injection.Calls');
uses('sabel.core.Exception');

class RecordRunningTimeInjection
{
  private $start;
  private $end;
  
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
  public function after($method, &$result)
  {
    if ($method == 'test') {
      $result = 'mocked!';
    } else if ($method == 'returnArray') {
      $result = array(0 => 'mocked!');
    }
  }
}

/**
 * test case for Sabel LW DI Container
 *
 * @author Mori Reo <mori.reo@servise.jp>
 */
class Test_SabelDI extends PHPUnit2_Framework_TestCase
{
  public function setUp()
  {
  }
  
  public function estLoad()
  {
    $c = new Sabel_Container_DI();
    $this->assertTrue(is_object($c));
    
    $object  = $c->load('Sabel_Core_Context');
    $o2 = $c->load('Data_Ditest_Module');
    
    $this->assertEquals('ModuleImpl result.', $o2->test('a'));
    
    $this->assertTrue(is_object($object));
  }
  
  public function estContainerInjection()
  {
    $c = new Sabel_Container_DI();
    $module = $c->loadInjected('Data_Ditest_Module');
    
    $ic = new Sabel_Injection_Calls();
    
    $runningTime = new RecordRunningTimeInjection();
    $ic->add($runningTime);
    $ic->add(new MockInjection());
    
    $module->bbs;
    $module->test('a', 'test');
    $this->assertTrue(is_float($runningTime->calcurate()));
    $this->assertTrue(is_array($module->returnArray()));
    $this->assertTrue(is_float($runningTime->calcurate()));
  }
  
  public function estMockedInjection()
  {
    $c = new Sabel_Container_DI();
    $module = $c->loadInjected('Data_Ditest_Module');
    $ic = new Sabel_Injection_Calls();
    $ic->addAfter(new MockInjection());
    $this->assertEquals('mocked!', $module->test('a'));
    $array = $module->returnArray();
    $this->assertEquals('mocked!', $array[0]);
  }
  
  public function estConvertClassName()
  {
    $this->assertEquals(convertClassPath('Ditest'), 'Ditest');
    $this->assertEquals(convertClassPath('Data_Ditest_Module_Test'), 'data.ditest.module.Test');
  }
  
  public function estAnnotation()
  {
    $c = new Sabel_Container_DI();
    $ar     = $c->load('Sabel_Annotation_Reader');
    $ic     = $c->load('Sabel_Injection_Calls');
    $module = $c->loadInjected('Data_Ditest_Module');
    
    $list = $ar->annotation('Data_Ditest_Module');
    
    $it = $list->iterator();
    
    while ($it->hasNext()) {
      $annotation = $it->next();
      $ic->add($annotation->createInjection());
    }
    
    $this->assertEquals('mocked!', $module->test('abc'));
  }
  
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