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
    return false;
  }
  
  public function testGetterInjection()
  {
    $c = new Container();
    $c->regist('test.Person', 'Person');
    $person = $c->load('test.Person');
    
    $f = $person->getFrastration(new FrastrationCalculator());
    $this->assertEquals('max', $f);
  }
  
  public function testComplexDependencyResolv()
  {
    return false;
  }
  
  public function testDirectoryPathToClassNameResolver()
  {
    $r = new DirectoryPathToClassNameResolver();
    $this->assertEquals('Sabel_Core_Router', $r->resolv('sabel/core/Router.php'));
    $this->assertEquals('Core_Router', $r->resolv('core/Router.php'));
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

class Person extends Mammalia implements TManager
{
  protected $age = null;
  
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
  
  public function getFrastration(FrastrationCalculator $c)
  {
    return $c->calc($this);
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