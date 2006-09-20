<?php

define('SABEL_CLASSES', RUN_BASE . '/cache/sabel_classes.php');
define('APP_CACHE',     RUN_BASE . '/cache/app.php');
define('LIB_CACHE',     RUN_BASE . '/cache/lib.php');
define('SCM_CACHE',     RUN_BASE . '/cache/schema.php');
define('INJ_CACHE',     RUN_BASE . '/cache/injection.php');

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
    $dt = new DirectoryTraverser(RUN_BASE);
    $remover = new SabelDirectoryAndFileRemover();
    $dt->visit($remover);
    $dt->traverse();
    $remover->removeEmptyDirectories();
  }
  
  public function testSabel()
  {
    $c = Container::initializeApplication();
    $fcontroller = $c->load('sabel.controller.Front');
    $this->assertTrue(is_object($fcontroller));
  }
}