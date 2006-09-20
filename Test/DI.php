<?php

require_once('PHPUnit2/Framework/TestCase.php');
require_once('Container.php');

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
  
  public function estTraverse()
  {
    $c  = new Container();
    $dt = new DirectoryTraverser('Test/data/testClassDirStructure');
    $dt->visit(new ClassRegister($c, 'root'));
    $dt->traverse();
    
    require('Test/data/testClassDirStructure/root/core/Controller.php');
    $obj = $c->load('root.core.Controller', 'singleton');
    $this->assertEquals('Root_Core_Controller', $obj->getClassName());
    
    $obj = $c->load('root.core.Controller');
    $this->assertEquals('Root_Core_Controller', $obj->getClassName());
  }
  
  // test case for class A depend class B.
  public function testSimpleDependencyResolve()
  {
    $c = new Container();
    $c->regist('test.Age', 'Age');
    $c->regist('test.Person', 'Person');
    
    $person = $c->load('test.Person');
    $this->assertEquals(15, $person->howOldAreYou('who', 'are you'));
  }
  
  public function testSetterInjection()
  {
    $c = new Container();
    $c->regist('test.Person', 'Person');
    $person = $c->load('test.Person');
    $this->assertEquals('max', $person->getFrastration());
  }
  
  public function testComplexDependencyResolv()
  {
    return false;
  }
  
  public function testDirectoryPathToClassNameResolver()
  {
    $this->assertEquals('Sabel_Core_Router',
                        NameResolver::resolvDirectoryPathToClassName('sabel/core/Router.php'));
    $this->assertEquals('Core_Router',
                        NameResolver::resolvDirectoryPathToClassName('core/Router.php'));
  }
}

interface TPerson
{
  function isMale();
}

abstract class Mammalia
{
  abstract function birth();
}

interface TManager extends TPerson
{
  function isManager();
}

class SomeClass
{
  /**
   * @implementation @ENVIRONMENT@_Exporter // -> ???_Exporter
   * @injection setter
   * @implementation @request:id@Calculator
   * @implementation @module@_Calculator
   * @injection setter setImplementation
   */
  protected $exporter = null;

  public function setExporter($exporter)
  {
    $this->exporter = $exporter;
  }
}

class Person extends Mammalia implements TManager
{
  protected $age = null;
  
  /**
   * @implementation FrastrationCalculator
   * @setter setFrastration
   */
  protected $frastration = null;
  
  /**
   *
   * @depend test.Age
   */
  public function __construct(Age $age)
  {
    $this->age = $age;
  }
  
  public function birth()
  {
    print "ogya-!";
  }
  
  public function howOldAreYou($arg, $arg2)
  {
    return $this->age->getAge();
  }
  
  public function getFrastration()
  {
    return $this->frastration->calc($this);
  }
  
  public function setFrastration($f)
  {
    $this->frastration = $f;
  }
  
  function isMale()
  {
    return true;
  }
  
  function isManager()
  {
    return true;
  }
}

class FrastrationCalculator
{
  public function calc($obj)
  {
    return 'max';
  }
}

class Age
{
  protected $age = 15;
  
  public function __construct()
  {
  }
  
  public function getAge()
  {
    return $this->age;
  }
  
  public function setAge($age = 0)
  {
    $this->age = $age;
  }
}