<?php

/**
 * define for application.
 */
define('SABEL_CLASSES', RUN_BASE . '/cache/sabel_classes.php');
define('APP_CACHE',     RUN_BASE . '/cache/app.php');
define('LIB_CACHE',     RUN_BASE . '/cache/lib.php');
define('SCM_CACHE',     RUN_BASE . '/cache/schema.php');
define('INJ_CACHE',     RUN_BASE . '/cache/injection.php');

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
  
  public function setUp()
  {
    mkdir(RUN_BASE);
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
    rmdir(RUN_BASE);
  }
  
  public function testSabel()
  {
    Sabel::initializeApplication();
    $c = Container::create();
    $fcontroller = $c->load('sabel.controller.Front');
    $this->assertTrue(is_object($fcontroller));
    
    ob_start();
    $fcontroller->ignition();
    $contents = rtrim(ob_get_clean());
    $this->assertEquals("welcome.\ntemplate missing", $contents);
  }
}