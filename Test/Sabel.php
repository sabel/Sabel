<?php

require ('generator/generator.php');
 
/**
 * TestCase for Sabel Aplication
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Sabel extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Sabel");
  }
  
  public function __construct()
  {
    if (is_dir(RUN_BASE)) {
      $dt = Sabel::load('Sabel_Util_DirectoryTraverser', RUN_BASE);
      $remover = new SabelDirectoryAndFileRemover();
      $dt->visit($remover);
      $dt->traverse();
      $remover->removeEmptyDirectories();
      rmdir(RUN_BASE);
    }
    
    if (!is_dir(RUN_BASE)) {
      mkdir(RUN_BASE);
      $dt = Sabel::load('Sabel_Util_DirectoryTraverser', SABEL_BASE.'/generator/skeleton');
      $dt->visit(new SabelDirectoryAndFileCreator());
      $dt->traverse();
    }
  }
  
  public function testSabel()
  {
    $fcontroller = Sabel::load('Sabel_Controller_Front');
    $this->assertTrue(is_object($fcontroller));
    
    set_include_path(get_include_path().':'.RUN_BASE.'/app/');
    $result = $fcontroller->ignition('/index/index');

    $this->assertEquals("welcome to Sabel have fun!\n", $result['html']);
  }
  
  /*
   * @todo make this.
  public function testLoad()
  {
    $classA = Sabel::load('Sabel_View');
    $classB = Sabel::load('Sabel_View', 'argA');
    $classC = Sabel::load('Sabel_View', 'argA', 'strArg');
    $classD = Sabel::load('Sabel_View', 'argA', array('arrayArg'));
    $classE = Sabel::load('Sabel_View', 'argA', new Sabel_View());
    
    $this->assertTrue(is_object($classA));
    $this->assertTrue(is_object($classB));
    $this->assertTrue(is_object($classC));
    $this->assertTrue(is_object($classD));
    $this->assertTrue(is_object($classE));
  }
  */
  
  public function testSingleton()
  {
    $classA = Sabel::load('Sabel_View_Renderer_Class');
    $classB = Sabel::load('Sabel_View_Renderer_Class');
    
    $this->assertNotSame($classA, $classB);
    
    $classA = Sabel::loadSingleton('Sabel_View_Renderer_Class');
    $classB = Sabel::loadSingleton('Sabel_View_Renderer_Class');
    
    $this->assertSame($classA, $classB);
  }
}
