<?php

/**
 * @category  Annotation
 * @author    Mori Reo <mori.reo@gmail.com>
 */
class Test_Annotation extends SabelTestCase
{
  private $reader = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Annotation");
  }
  
  public function setUp()
  {
    $this->reader = new Sabel_Annotation_Reader();
  }
  
  public function testClassAnnotation()
  {
    $annotations = $this->reader->readClassAnnotation("TestAnnotation");
    $this->assertEquals("class", $annotations["annotation"][0][0]);
  }
  
  public function testNormalValue()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod");
    $this->assertEquals("value", $annotation["param"][0][0]);
  }
  
  public function testMultiValue()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod");
    $this->assertEquals("hoge", $annotation["array"][0][0]);
    $this->assertEquals("fuga", $annotation["array"][0][1]);
  }
  
  public function testIgnoreSpace()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod2");
    $this->assertEquals("value", $annotation["ignoreSpace"][0][0]);
  }
  
  public function testQuotedValue()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod2");
    
    $this->assertEquals("hoge", $annotation["array"][0][0]);
    $this->assertEquals('  test"a"  ', $annotation["array"][0][1]);
    $this->assertEquals("a: index", $annotation["array"][0][2]);
    $this->assertEquals("fuga", $annotation["array"][1][0]);
    $this->assertEquals("  test'a'  ", $annotation["array"][1][1]);
    $this->assertEquals("c: index, a: index", $annotation["array"][1][2]);
  }
  
  public function testEmptyValue()
  {
    $annotation = $this->reader->readMethodAnnotation("TestAnnotation", "testMethod3");
    $this->assertTrue(isset($annotation["emptyValue"]));
    $this->assertNull($annotation["emptyValue"][0]);
  }
}

/**
 * class annotation
 *
 * @annotation class
 */
class TestAnnotation
{
  /**
   * this is annotation test
   *
   * @param value
   * @array hoge fuga
   */
  public function testMethod($test, $test = null)
  {
    
  }
  
  /**
   * this is annotation test
   *
   * @ignoreSpace       value
   * @array hoge '  test"a"  ' "a: index"
   * @array fuga "  test'a'  " 'c: index, a: index'
   */
  public function testMethod2()
  {
    
  }
  
  /**
   * @emptyValue
   */
  public function testMethod3()
  {
    
  }
}
