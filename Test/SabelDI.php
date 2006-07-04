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
  
  public function executeAfter($method, &$result)
  {
    $this->end = microtime();
  }
  
  public function calcurate()
  {
    return ($this->end - $this->start);
  }
}

class MockInjection implements InjectionCall
{
  public function executeBefore($method, $arg)
  {
    
  }
  public function executeAfter($method, &$result)
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
    
    $ic = new InjectionCalls();
    
    $runningTime = new RecordRunningTimeInjection();
    $ic->addBoth($runningTime);
    
    $module->test('a', 'test');
    $this->assertTrue(is_float($runningTime->calcurate()));
    $this->assertTrue(is_array($module->returnArray()));
    $this->assertTrue(is_float($runningTime->calcurate()));
  }
  
  public function testMockedInjection()
  {
    $c = new SabelDIContainer();
    $module = $c->loadInjected('Ditest_Module');
    $ic = new InjectionCalls();
    $ic->addAfter(new MockInjection());
    $this->assertEquals('mocked!', $module->test('a'));
    $array = $module->returnArray();
    $this->assertEquals('mocked!', $array[0]);
  }
  
  public function testConvertClassName()
  {
    $this->assertEquals(convertClassPath('Ditest'), 'Ditest');
    $this->assertEquals(convertClassPath('Ditest_Module_Test'), 'ditest.module.Test');
  }
  
  public function testAnnotation()
  {
    $c = new SabelDIContainer();
    $anno   = $c->load('Sabel_Annotation');
    $ic     = $c->load('InjectionCalls');
    $module = $c->loadInjected('Ditest_Module');
    
    $it = $anno->annotation('Ditest_ModuleImpl');
    while ($it->hasNext()) $ic->addBoth($it->next());
    
    $this->assertEquals('mocked!', $module->test('abc'));
  }
}

class Sabel_ArrayList
{
  private $array;
  
  public function __construct($array = null) {
    if ($array) $this->array = $array;
  }
  
  public function push($value)
  {
    $this->array[] = $value;
  }
  
  public function pop()
  {
    return array_pop($this->array);
  }
  
  public function set($key, $value) {
    $this->array[$key] = $value;
  }
  
  public function get($key)
  {
    return $this->array[$key];
  }
  
  public function iterator()
  {
    return new Sabel_Iterator($this->array);
  }
  
  public function toArray()
  {
    return $this->array;
  }
}

class Sabel_Iterator
{
  private $array;
  private $count;
  
  public function __construct($array) {
    $this->count = 0;
    $this->array = $array;
  }
  
  public function hasNext()
  {
    return ($this->count < count($this->array));
  }
  
  public function next()
  {
    $value = $this->array[$this->count];
    $this->count++;
    return $value;
  }
  
  public function prev()
  {
    $value = $this->array[$this->count];
    $this->count--;
    return $value;
  }
  
  public function toArray()
  {
    return $this->array;
  }
}

class Sabel_Annotation
{
  protected $list = null;
  
  /**
   * default constructer
   *
   * @param void
   */
  public function __construct()
  {
    $this->list = new Sabel_ArrayList;
  }
  
  public function annotation($className)
  {
    $rModule = new ReflectionClass($className);
    foreach ($rModule->getMethods() as $midx => $method) {
      $this->processMethod($method->getDocComment());
    }
    
    return $this->list->iterator();
  }
  
  protected function processMethod($comment)
  {
    $comments = preg_split("/[\n\r]/", $comment, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($comments as $line) {
      $this->processAnnotation($line);
    }
  }
  
  protected function processAnnotation($line)
  {
    $annotation = split(' ', $this->removeComment($line));
    if ($annotation[0] == '@injection') {
      $className = $annotation[1];
      if (class_exists($className)) $this->list->push(new $className());
    }
  }
  
  protected function removeComment($line)
  {
    $line =     preg_replace('/^\*/',     '', trim($line));
    $line =     preg_replace('/\*\/$/',   '',      $line);
    return trim(preg_replace('/^\/\*\*/', '',      $line));
  }
}