<?php

require ('generator/generator.php');
 
/**
 * TestCase for Sabel Aplication
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Sabel extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Sabel");
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
    $fcontroller->processCandidate();
    $this->assertTrue(is_object($fcontroller));
    
    set_include_path(get_include_path().':'.RUN_BASE.'/app/');
    $request = Sabel::load("Sabel_Request_Web", "/index/index");
    $this->assertTrue(is_object($fcontroller->ignition($request)));
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
  
  public function testArray()
  {
    $array = __("keyA valueA, keyB valueB");
    $this->assertEquals(array("keyA"=>"valueA","keyB"=>"valueB"), $array);
    
    $array = __("keyA (keyB valueA), __TRUE__");
    $this->assertEquals(array("keyA"=>array("keyB"=>"valueA"),__TRUE__), $array);
    
    $array = __("keyA 'valueA', (valueB, __FALSE__)");
    $this->assertEquals(array("keyA"=>"valueA", array("valueB", __FALSE__)), $array);
    
    // @todo this assert fail on PHP 5.2.1RC4 of MacOSX
    $array = __("keyA 'value()', keyB ('valueB')");
    $this->assertEquals(array("keyA"=>"value()","keyB"=>array("valueB")), $array);
    
    $array = __("keyA ((keyB valueA))");
    $this->assertEquals(array("keyA"=>array(array("keyB"=>"valueA"))), $array);
  }
}
