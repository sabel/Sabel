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
  
  public function testTraverse()
  {
    $c  = new Container();
    $dt = new DirectoryTraverser('Test/data/testClassDirStructure');
    // $dt->visit(new ClassCombinator());
    $dt->visit(new ClassRegister($c));
    $dt->traverse();
    
    require('Test/data/testClassDirStructure/root/core/Controller.php');
    $obj = $c->load('root.core.Controller', 'singleton');
    $ref = new ReflectionClass($obj);
    $this->assertEquals('Root_Core_Controller', $ref->getName());
    
    //$c->switchNameSpace('root.core');
    //$obj = $c->load('Controller', 'singleton');
    //$ref = new ReflectionClass($obj);
    //$this->assertEquals('Root_Core_Controller', $ref->getName());
    
    $this->assertTrue(is_readable('allclasses.php'));
  }
  
  public function testDirectoryPathToClassNameResolver()
  {
    $r = new DirectoryPathToClassNameResolver();
    $this->assertEquals('Sabel_Core_Router', $r->resolv('sabel/core/Router.php'));
    $this->assertEquals('Core_Router', $r->resolv('core/Router.php'));
  }
}