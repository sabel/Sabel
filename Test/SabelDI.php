<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('sabel/Functions.php');
require_once('sabel/core/Context.php');

require_once('sabel/container/DI.php');
require_once('sabel/core/spyc.php');

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
    uses('sabel.injection.Calls');
    
    uses('sabel.core.Exception');
  }
  
  public function testLoad()
  {
    Sabel_Core_Context::addIncludePath('');
    Sabel_Core_Context::addIncludePath('core/');
    
    $c = new Sabel_Container_DI();
    $this->assertTrue(is_object($c));
    
    $object  = $c->load('Sabel_Core_Context');
    $o2 = $c->load('Sabel_Ditest_Module');
    
    $this->assertEquals('ModuleImpl result.', $o2->test('a'));
    
    $this->assertTrue(is_object($object));
  }
  
  public function testContainerInjection()
  {
    $c = new Sabel_Container_DI();
    $module = $c->loadInjected('Sabel_Ditest_Module');
    
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
    $c = new Sabel_Container_DI();
    $module = $c->loadInjected('Sabel_Ditest_Module');
    $ic = new Sabel_Injection_Calls();
    $ic->addAfter(new MockInjection());
    $this->assertEquals('mocked!', $module->test('a'));
    $array = $module->returnArray();
    $this->assertEquals('mocked!', $array[0]);
  }
  
  public function testConvertClassName()
  {
    $this->assertEquals(convertClassPath('Ditest'), 'Ditest');
    $this->assertEquals(convertClassPath('Sabel_Ditest_Module_Test'), 'sabel.ditest.module.Test');
  }
  
  public function testAnnotation()
  {
    $c = new Sabel_Container_DI();
    $ar     = $c->load('Sabel_Annotation_Reader');
    $ic     = $c->load('Sabel_Injection_Calls');
    $module = $c->loadInjected('Sabel_Ditest_Module');
    
    $list = $ar->annotation('Sabel_Ditest_Module');
    
    $it = $list->iterator();
    
    while ($it->hasNext()) {
      $annotation = $it->next();
      $ic->add($annotation->createInjection());
    }
    
    $this->assertEquals('mocked!', $module->test('abc'));
  }
}





