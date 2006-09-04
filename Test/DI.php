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
  public function testDependencyResolve()
  {
    $c = new Container();
    $c->regist('test.Age', 'Age');
    $c->regist('test.Person', 'Person');
    
    $person = $c->load('test.Person');
  }
  
  public function testDirectoryPathToClassNameResolver()
  {
    $r = new DirectoryPathToClassNameResolver();
    $this->assertEquals('Sabel_Core_Router', $r->resolv('sabel/core/Router.php'));
    $this->assertEquals('Core_Router', $r->resolv('core/Router.php'));
  }
}

class Person
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
}

class Age
{
  protected $age = 0;
  
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