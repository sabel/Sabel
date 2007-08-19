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
    
    $this->assertEquals($annotation["annotation"], "class");
    
    $annotations = $reader->readMethods("Test_Annotation_Class");
    $this->assertEquals($annotations["testMethod"]["normal"], "test1");
    $this->assertEquals($annotations["testMethod"]["ignoreSpace"], "test2");
  }
  
  public function testDuplicateEntry()
  {
    $reader = new Sabel_Annotation_Reader();
    try {
      $reader->read("Test_Annotation_Duplicate");
      $this->fail();
    } catch (Exception $e) {
      $this->assertTrue(true, $e->getMessage());
    }
  }
  
  public function testMethodDuplicateEntry()
  {
    $reader = new Sabel_Annotation_Reader();
    try {
      $reader->readMethods("Test_Annotation_Duplicate");
      $this->fail();
    } catch (Exception $e) {
      $this->assertTrue(true, $e->getMessage());
    }
  }
  
  public function testAnnotationReflectionClass()
  {
    $reflect = new Sabel_Annotation_ReflectionClass("Test_Annotation_Class");
    $annot = $reflect->getAnnotation("annotation");
    $this->assertEquals($annot, "class");
  }
  
  public function testAnnotationReflectionMethod()
  {
    $reflect = new Sabel_Annotation_ReflectionClass("Test_Annotation_Class");
    $methods = $reflect->getMethodsAsAssoc();
    
    $this->assertTrue(is_array($methods));
    
    $testMethod = $methods["testMethod"];
    $this->assertEquals(2, $testMethod->getNumberOfParameters());
    $this->assertEquals("test1", $testMethod->getAnnotation("normal"));
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
   * @array      test4 elem1 elem2 elem3
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
 * @annotation dup
 * @annotation dup
 */
class Test_Annotation_DupulicateEntry
{
  /**
   * this is annotation test
   *
   * @dup dup
   * @dup dup
   */
  public function testMethod()
  {
  }
}
