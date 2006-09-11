<?php

/**
 * Test_Container
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Container extends SabelTestCase
{
  private $c = null;
  
  public static function suite()
  {
   return new PHPUnit2_Framework_TestSuite("Test_Container");
  }
  
  public function __construct()
  {
    $this->c = Container::create();
    $this->c->regist('target.test.Class', 'Target_Test_Class');
  }
  
  public function testClassLoading()
  {
    $obj = $this->c->load('target.test.Class');
    $this->assertEquals('Target_Test_Class', $obj->whatYourName());
  }
  
  public function testClassLoadingWithShortClassName()
  {
    $this->c->regist('foo.shortcut.test.Class', 'Shortcut_Test_Class');
    $obj = $this->c->load('foo.shortcut.test.Class');
    $this->assertEquals('Foo_Shortcut_Test_Class', $obj->whatYourName());
  }
  
  public function testSetterInjection()
  {
    $this->c->regist('test.Property', 'Test_Property');
    $this->c->regist('test.setter.Injection', 'Test_Setter_Injection');
    
    $obj = $this->c->load('test.setter.Injection');
    $this->assertEquals($obj->whatYourPropertyName(), 'Test_Property');
  }
}

/**  bellow classes are TestCase use only. **/

class Shortcut_Test_Class
{
  public function __construct()
  {
    
  }
  
  public function whatYourName()
  {
    return 'Foo_Shortcut_Test_Class';
  }
}

class Target_Test_Class
{
  public function __construct()
  {
    
  }
  
  public function whatYourName()
  {
    return 'Target_Test_Class';
  }
}

class Test_Property
{
  public function whatYourName()
  {
    $ref = new ReflectionClass($this);
    return $ref->getName();
  }
}

/**
 * Test class for Setter Injection.
 */
class Test_Setter_Injection
{
  /**
   * @implementation Test_Property
   * @setter setProperty
   */
   protected $property = null;
   
   public function setProperty($property)
   {
     $this->property = $property;
   }
   
   public function whatYourName()
   {
     $ref = new ReflectionClass($this);
     return $ref->getName();
   }
   
   public function whatYourPropertyName()
   {
     return $this->property->whatYourName();
   }
}