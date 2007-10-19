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
    $p = new Vircle();
    $r = $p->getReflection();
    $an = $r->getAnnotation("test");
    $this->assertEquals(array(array("test")), $an);
  }
  
  public function testReflectionMethod()
  {
    $p = new Vircle();
    $r = $p->getReflection();
    $method = $r->getMethod("getName");
    $an = $method->getAnnotation("test");
    $this->assertEquals(array(array("test")), $an);
  }
}

/**
 * @test test
 */
class Vircle extends Sabel_Object
{
  /**
   * @test test
   */
  public function getName()
  {
    
  }
}