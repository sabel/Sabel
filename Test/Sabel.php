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
      $dt = new DirectoryTraverser(RUN_BASE);
      $remover = new SabelDirectoryAndFileRemover();
      $dt->visit($remover);
      $dt->traverse();
      $remover->removeEmptyDirectories();
      rmdir(RUN_BASE);
    }
    
    if (!is_dir(RUN_BASE)) {
      mkdir(RUN_BASE);
      $dt = new DirectoryTraverser(SABEL_BASE . '/generator/skeleton');
      $dt->visit(new SabelDirectoryAndFileCreator());
      $dt->traverse();
    }
  }
  
  public function testSabel()
  {
    ob_start();
    $fcontroller = Sabel::load('Sabel_Controller_Front');
    $this->assertTrue(is_object($fcontroller));
    
    set_include_path(get_include_path().':'.RUN_BASE.'/app/');
    $fcontroller->ignition('/index/index');
    $contents = rtrim(ob_get_clean());
    $this->assertEquals("welcome.", $contents);
  }
  
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
