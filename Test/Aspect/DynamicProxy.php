<?php

/**
 * TestCase of sabel.aspect.pointcuts
 *
 * @author Mori Reo <mori.reo@sabel.jp>
 */
class Test_Aspect_DynamicProxy extends Test_Aspect_Base
{
  public static function suite()
  {
    return self::createSuite("Test_Aspect_DynamicProxy");
  }
  
  public function setUp()
  {
    $this->weaver = new Sabel_Aspect_DynamicWeaver("Sabel_Tests_Aspect_TargetClass");
  }
  
  public function testDynamicWeaveGetClassName()
  {
    $weaver = $this->weaver;
    
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    $target = $weaver->getProxy();
    
    $this->assertEquals($target->getClassName(), "Sabel_Tests_Aspect_TargetClass");
  }
  
  public function testDynamicWeaveClass()
  {
    $weaver = $this->weaver;
    
    $weaver->setTarget("Sabel_Tests_Aspect_TargetClass");
    $target = $weaver->getProxy();
    
    $this->assertEquals(get_class($target), "Sabel_Aspect_DefaultProxy");
  }
}