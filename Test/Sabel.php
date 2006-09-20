<?php

class Test_Sabel extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Sabel");
  }
  
  public function setUp()
  {
    require_once('generator/generator.php');
    $dt = new DirectoryTraverser(SABEL_BASE . '/generator/skeleton');
    $dt->visit(new SabelDirectoryAndFileCreator());
    $dt->traverse();
  }
  
  public function tearDown()
  {
    
  }
  
  public function testSabel()
  {
    $c = Container::create();
  }
}