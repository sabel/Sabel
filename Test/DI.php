<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

Sabel_Core_Context::addIncludePath('');
require_once('sabel/config/Spyc.php');
require_once('sabel/container/DI.php');
require_once('sabel/exception/Runtime.php');
require_once('sabel/injection/Calls.php');

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

/**
 * test case for Sabel LW DI Container
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_DI extends PHPUnit2_Framework_TestCase
{
  protected $c = null;
  
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_DI");
  }
  
  public function setUp()
  {
    $this->c = new Sabel_Container_DI();
  }
  
  public function tearDown()
  {
    unset($this->c);
  }
  
  public function testDummy()
  {
    return true;
  }
  
  /*
  public function testNotInjectedLoad()
  {
    $o2 = $this->c->load('Data_Ditest_Module');
    
    $this->assertEquals('ModuleImpl result.', $o2->test('a'));
  }
  
  public function testInjectedLoad()
  {
    $obj = $this->c->loadInjected('Data_Ditest_Module');
    $this->assertEquals('ModuleImpl result.', $obj->test('a'));
  }
  
  public function testContainerInjection()
  {
    $module = $this->c->loadInjected('Data_Ditest_Module');
    
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
  
  public function testMockedInjection()
  {
    $module = $this->c->loadInjected('Data_Ditest_Module');
    $ic = new Sabel_Injection_Calls();
    $ic->addAfter(new MockInjection());
    $this->assertEquals('mocked!', $module->test('a'));
    $array = $module->returnArray();
    $this->assertEquals('mocked!', $array[0]);
  }
  
  public function testAnnotation()
  {
    $ar     = $this->c->load('Sabel_Annotation_Reader');
    $ic     = $this->c->load('Sabel_Injection_Calls');
    $module = $this->c->loadInjected('Data_Ditest_Module');
    
    $list = $ar->annotation('Data_Ditest_Module');
    
    $it = $list->iterator();
    
    while ($it->hasNext()) {
      $annotation = $it->next();
      $ic->add($annotation->createInjection());
    }
    
    $this->assertEquals('mocked!', $module->test('abc'));
  }
  */
}