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
  public static function suite()
  {
    return new PHPUnit_Framework_TestSuite("Test_Annotation");
  }
  
  public function setUp()
  {
  }
  
  public function tearDown()
  {
  }
  
  public function testAnnotationReader()
  {
    $reader = new Sabel_Annotation_Reader();
    $annotation = $reader->read("Test_Annotation_Class");
    $expected = array("annotation" => array(array("class")));
    $this->assertEquals($expected, $annotation);
    
    $annotations = $reader->readMethods("Test_Annotation_Class");
    $method = $annotations["testMethod"];
    $this->assertEquals(array(array("test1")), $method["normal"]);
    $this->assertEquals(array(array("test2")), $method["ignoreSpace"]);
    
    $this->assertEquals("test4", $method["array"][0][0]);
    $this->assertEquals("elem1", $method["array"][0][1]);
    $this->assertEquals("a: index", $method["array"][0][2]);
  }
  
  public function testDuplicateEntry()
  {
    $reader = new Sabel_Annotation_Reader();
    
    $annotations = $reader->read("Test_Annotation_Dupulicate");
    
    $expected = array("annotation" => array(
                                        array("dupOne"),
                                        array("dupTwo", "two"),
                                        array("dupThree")
                                      ));
    $this->assertEquals($expected, $annotations);
  }
  
  public function testAnnotationReflectionClass()
  {
    $reflect = new Sabel_Annotation_ReflectionClass("Test_Annotation_Class");
    $annot = $reflect->getAnnotation("annotation");
    $this->assertEquals(array(array("class")), $annot);
  }
  
  public function testAnnotationReflectionMethod()
  {
    $reflect = new Sabel_Annotation_ReflectionClass("Test_Annotation_Class");
    $methods = $reflect->getMethodsAsAssoc();
    
    $this->assertTrue(is_array($methods));
    
    $testMethod = $methods["testMethod"];
    $this->assertEquals(2, $testMethod->getNumberOfParameters());
    $this->assertEquals(array(array("test1")), $testMethod->getAnnotation("normal"));
    $this->assertTrue(is_array($testMethod->getAnnotation("array")));
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
   * @normal test1
   * @ignoreSpace   test2
   * @array      test4 elem1 "a: index"
   */
  public function testMethod($test, $test = null)
  {
  }
  
  /**
   * this is annotation test
   *
   * @normal test1
   * @ignoreSpace   test2
   * @array      test4 elem1 elem2 elem3
   */
  public function testMethodTwo($test, $test = null)
  {
  }
}

/**
 * test for duplicate entry
 *
 * @annotation dupOne
 * @annotation dupTwo two
 * @annotation dupThree
 */
class Test_Annotation_Dupulicate
{
  /**
   * this is annotation test
   *
   * @dup dupOne
   * @dup dupTwo
   */
  public function testMethod()
  {
  }
}
