<?php

require_once('PHPUnit2/Framework/TestCase.php');

class Test_Annotation extends PHPUnit2_Framework_TestCase
{
  protected $c;
  
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Annotation");
  }
  
  public function __construct()
  {
    $this->c = new Container();
  }
  
  public function testAnnotation()
  {
    $ar   = $this->c->load('sabel.annotation.Reader');
    $list = $ar->annotation('AnnotatedTestClass');
    
    $it = $list->iterator();
    while ($it->hasNext()) {
      $annotation = $it->next();
      if (is_null($annotation)) continue;
      switch ($annotation->getName()) {
        case 'annot':
          $this->assertEquals('test1', $annotation->getContents());
        break;
        case 'annot2':
          $this->assertEquals('test2', $annotation->getContents());
        break;
        case 'annot3':
          $this->assertEquals('test3', $annotation->getContents());
        break;
        case 'annot4':
          $this->assertTrue(is_array($annotation->getContents()));
        break;
      }
    }
  }
}

/**
 * class annotation
 *
 * @annotation class
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
   */
  public function testMethod()
  {
    
  }
}