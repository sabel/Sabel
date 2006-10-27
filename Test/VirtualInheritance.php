<?php

/**
 * Test_VirtualInheritance
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_VirtualInheritance extends SabelTestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_VirtualInheritance");
  }
  
  public function __construct()
  {
    
  }
  
  public function testVirtualInheritance()
  {
    $c = Container::create();
    $c->regist('test.InheritedClass',        'InheritedClass');
    $c->regist('test.VirtualParentClass',    'VirtualParentClass');
    $c->regist('test.VirtualParentClassTwo', 'VirtualParentClassTwo');
    
    $inherited = new Sabel_Aspect_VirtualInheritProxy(new InheritedClass());
    
    $inherited->inherit('test.VirtualParentClass')
              ->inherit('test.VirtualParentClassTwo');
    
    $this->assertEquals('test.Inherited',             $inherited->doSomething());
    $this->assertEquals('test.VirtualParentClass',    $inherited->parentMethod());
    $this->assertEquals('test.VirtualParentClassTwo', $inherited->parentMethodTwo());
  }
}

class VirtualParentClass
{
  public function parentMethod()
  {
    return 'test.VirtualParentClass';
  }
}

class VirtualParentClassTwo
{
  public function parentMethodTwo()
  {
    return 'test.VirtualParentClassTwo';
  }
}

class InheritedClass
{
  public function doSomething()
  {
    return 'test.Inherited';
  }
}