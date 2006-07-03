<?php

require_once('PHPUnit2/Framework/TestCase.php');

// must need by DI
require_once('core/SabelDIContainer.php');
require_once('core/functions.php');
require_once('core/spyc.php');
require_once('core/SabelException.php');

// Test Class
require_once('core/SabelContext.php');


class RecordRunningTimeInjection implements InjectionCall
{
  private $start;
  private $end;
  
  public function executeBefore($method, $arg)
  {
    $this->start = microtime();
  }
  
  public function executeAfter($method, $result)
  {
    $this->end = microtime();
  }
  
  public function calcurate()
  {
    $startParts = split(' ', $this->start);
    $endParts   = split(' ', $this->end);
    
    return ($this->end - $this->start);
  }
}

/**
 * test case for SabelPager
 *
 * @author Mori Reo <mori.reo@servise.jp>
 */
class Test_SabelDI extends PHPUnit2_Framework_TestCase
{
  public function testLoad()
  {
    SabelContext::addIncludePath('core/');
    
    $c = new SabelDIContainer();
    $this->assertTrue(is_object($c));
    
    $object  = $c->load('SabelContext');
    $o2 = $c->load('Ditest_Module');
    
    $this->assertEquals('ModuleImpl result.', $o2->test('a'));
    
    $this->assertTrue(is_object($object));
  }
  
  public function testContainerInjection()
  {
    $c = new SabelDIContainer();
    $module = $c->loadInjected('Ditest_Module');
    
    $i = new InjectionCalls();
    
    $runningTime = new RecordRunningTimeInjection();
    $i->addBoth($runningTime);
    
    $module->test('a');
    
    print "\nRunningTime: " . $runningTime->calcurate() . "\n";
  }
  
  public function testConvertClassName()
  {
    $this->assertEquals(convertClassPath('Ditest'), 'Ditest');
    $this->assertEquals(convertClassPath('Ditest_Module_Test'), 'ditest.module.Test');
  }
}