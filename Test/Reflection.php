<?php

/**
 * TestCase for Sabel Aplication
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Reflection extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Reflection");
  }
    
  public function testReflection()
  {
    $o = new Vircle();
    $reflection = $o->getReflection();
    
    $this->assertTrue($reflection->hasAnnotation("hoge"));
    $this->assertTrue($reflection->hasAnnotation("class"));
    
    $annotation = $reflection->getAnnotation("class");
    $this->assertEquals("value", $annotation[0][0]);
  }
  
  public function testReflectionMethod()
  {
    $o = new Vircle();
    $reflection = $o->getReflection()->getMethod("fooMethod");
    
    $this->assertTrue($reflection->hasAnnotation("fuga"));
    $this->assertTrue($reflection->hasAnnotation("method"));
    
    $annotation = $reflection->getAnnotation("method");
    $this->assertEquals("value", $annotation[0][0]);
  }
  
  public function testMethodAnnotation()
  {
    $o = new Vircle();
    $annotation = $o->getReflection()->getMethodAnnotation("fooMethod", "method");
    $this->assertEquals("value", $annotation[0][0]);
  }
}

/**
 * @hoge
 * @class value
 */
class Vircle extends Sabel_Object
{
  /**
   * @fuga
   * @method value
   */
  public function fooMethod()
  {
    
  }
}
