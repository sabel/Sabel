<?php

/**
 * Test_Annotation
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
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
    $annotations = $this->reader->read("Test_Annotation_Class");
    $this->assertEquals("class", $annotations["annotation"][0][0]);
  }
  
  public function testNormalValue()
  {
    $annotations = $this->reader->readMethods("Test_Annotation_Class");
    $annotation  = $annotations["testMethod"];
    $this->assertEquals("value", $annotation["param"][0][0]);
  }
  
  public function testMultiValue()
  {
    $annotations = $this->reader->readMethods("Test_Annotation_Class");
    $annotation  = $annotations["testMethod"];
    $this->assertEquals("hoge", $annotation["array"][0][0]);
    $this->assertEquals("fuga", $annotation["array"][0][1]);
  }
  
  public function testIgnoreSpace()
  {
    $annotations = $this->reader->readMethods("Test_Annotation_Class");
    $annotation  = $annotations["testMethod2"];
    $this->assertEquals("value", $annotation["ignoreSpace"][0][0]);
  }
  
  public function testQuotedValue()
  {
    $annotations = $this->reader->readMethods("Test_Annotation_Class");
    $annotation  = $annotations["testMethod2"];
    $this->assertEquals("hoge", $annotation["array"][0][0]);
    $this->assertEquals('  test"a"  ', $annotation["array"][0][1]);
    $this->assertEquals("a: index", $annotation["array"][0][2]);
    $this->assertEquals("fuga", $annotation["array"][1][0]);
    $this->assertEquals("  test'a'  ", $annotation["array"][1][1]);
    $this->assertEquals("c: index, a: index", $annotation["array"][1][2]);
  }
}

/**
 * class annotation
 *
 * @annotation class
 */
class Test_Annotation_Class
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
}
