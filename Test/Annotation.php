<?php

require_once('PHPUnit2/Framework/TestCase.php');

class Test_Annotation extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Annotation");
  }
  
  public function __construct()
  {
    
  }
  
  public function setUp()
  {
  }
  
  public function tearDown()
  {
    
  }
  
  public function testAnnotation()
  {
    $ar   = Sabel::load('Sabel_annotation_Reader');
    $list = $ar->annotation('AnnotatedTestClass');
    
    foreach ($list as $annotation) {
      
      if (!is_object($annotation)) continue;
      
      switch ($annotation->getName()) {
        case 'annotclass':
          $this->assertEquals('annotclass', $annotation->getContents());
          break;
        case 'annot':
          $this->assertEquals('test1', $annotation->getContents());
          break;
        case 'annot2':
          $this->assertEquals('test2', $annotation->getContents());
          break;
        case 'annot3':
          $this->assertEquals('test3', $annotation->getContents());
          $this->assertFalse(is_object($annotation->createInjection()));
          break;
        case 'annot4':
          $this->assertTrue(is_array($annotation->getContents()));
          break;
        case 'injection':
          $this->assertTrue(is_object($annotation->createInjection()));
          break;
      }
    }
  }
  
  /**
   *
   * @todo reimplement
   */
  public function estByName()
  {
    $sameName = $ar->getAnnotationsByName('AnnotatedTestClass', 'same');
    foreach ($sameName as $entry) {
      $this->assertEquals('value', $entry->getContents());
    }
  }
}

/**
 * class annotation
 *
 * @annotclass annotclass
 */
class AnnotatedTestClass
{
  /**
   * this is annotation test
   *
   * @annot  test1
   * @annot2   test2
   * @annot3    test3
   * @annot4      test4 elem1 elem2 elem3
   * @injection  AnnotationsInjectionTestClass
   * @same value
   * @same value
   */
  public function testMethod()
  {
    
  }
}

class AnnotationsInjectionTestClass
{
  public function testMethod()
  {
  }
}
