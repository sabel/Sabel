<?php

class Test_View_TemplateFile extends Test_View_Tests
{
  public static function suite()
  {
    return self::createSuite("Test_View_TemplateFile");
  }
  
  public function testSetup()
  {
    $base = dirname(__FILE__) . DS . "templates";
    if (!defined("MODULES_DIR_PATH")) define("MODULES_DIR_PATH", $base);
    
    $repository = $this->createRepository("hoge");
    
    $this->assertEquals(3, count($repository->getTemplates()));
    $this->assertTrue($repository->getTemplate("controller") instanceof Sabel_View_Template);
    $this->assertTrue($repository->getTemplate("module") instanceof Sabel_View_Template);
    $this->assertTrue($repository->getTemplate("app") instanceof Sabel_View_Template);
    $this->assertNull($repository->getTemplate("hoge"));
  }
  
  protected function createRepository($controllerName)
  {
    $controller = new Sabel_View_Template_File("index" . DS . VIEW_DIR_NAME . DS . $controllerName . DS);
    $repository = new Sabel_View_Repository("controller", $controller);
    
    $module = new Sabel_View_Template_File("index" . DS . VIEW_DIR_NAME . DS);
    $repository->addTemplate("module", $module);
    
    $app = new Sabel_View_Template_File(VIEW_DIR_NAME . DS);
    $repository->addTemplate("app", $app);
    
    return self::$repository = $repository;
  }
}
