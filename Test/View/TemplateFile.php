<?php

define("VIEW_DIR_NAME", "views");
define("TPL_SUFFIX", ".tpl");

if (!defined("DS")) {
  define("DS", DIRECTORY_SEPARATOR);
}

class Test_View_TemplateFile extends SabelTestCase
{
  private static $repository = null;
  
  public static function suite()
  {
    return self::createSuite("Test_View_TemplateFile");
  }
  
  public function testSetup()
  {
    $base = dirname(__FILE__) . DS . "templates";
    define("MODULES_DIR_PATH", $base);
    
    $repository = $this->createRepository("hoge");
    
    $this->assertEquals(3, count($repository->getTemplates()));
    $this->assertTrue($repository->getTemplate("controller") instanceof Sabel_View_Template);
    $this->assertTrue($repository->getTemplate("module") instanceof Sabel_View_Template);
    $this->assertTrue($repository->getTemplate("app") instanceof Sabel_View_Template);
    $this->assertNull($repository->getTemplate("hoge"));
  }
  
  public function testValidTemplate()
  {
    $template = self::$repository->getValidTemplate("index");
    $this->assertTrue($template instanceof Sabel_View_Template);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "hoge" . DS . "index" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$repository->getValidTemplate("hoge");
    $this->assertTrue($template instanceof Sabel_View_Template);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "hoge" . DS . "hoge" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$repository->getValidTemplate("error");
    $this->assertTrue($template instanceof Sabel_View_Template);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "error" . TPL_SUFFIX, $template->getPath());
  }
  
  public function testInvalidTemplate()
  {
    $template = self::$repository->getValidTemplate("fuga");
    $this->assertNull($template);
    
    $template = self::$repository->getValidTemplate("abcdef");
    $this->assertNull($template);
  }
  
  public function testSetup2()
  {
    $repository = $this->createRepository("fuga");
    
    $this->assertEquals(3, count($repository->getTemplates()));
    $this->assertTrue($repository->getTemplate("controller") instanceof Sabel_View_Template);
    $this->assertTrue($repository->getTemplate("module") instanceof Sabel_View_Template);
    $this->assertTrue($repository->getTemplate("app") instanceof Sabel_View_Template);
    $this->assertNull($repository->getTemplate("fuga"));
  }
  
  public function testValidTemplate2()
  {
    $template = self::$repository->getValidTemplate("index");
    $this->assertTrue($template instanceof Sabel_View_Template);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "fuga" . DS . "index" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$repository->getValidTemplate("fuga");
    $this->assertTrue($template instanceof Sabel_View_Template);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "fuga" . DS . "fuga" . TPL_SUFFIX, $template->getPath());
    
    $template = self::$repository->getValidTemplate("error");
    $this->assertTrue($template instanceof Sabel_View_Template);
    $this->assertEquals(MODULES_DIR_PATH . DS . "index" . DS . VIEW_DIR_NAME . DS . "error" . TPL_SUFFIX, $template->getPath());
  }
  
  public function testInvalidTemplate2()
  {
    $template = self::$repository->getValidTemplate("hoge");
    $this->assertNull($template);
    
    $template = self::$repository->getValidTemplate("abcdef");
    $this->assertNull($template);
  }
  
  public function testGetContents()
  {
    $template = self::$repository->getValidTemplate("index");
    $contents = $template->getContents();
    $this->assertEquals("fuga/index.tpl", rtrim($contents));
    
    $template = self::$repository->getValidTemplate("fuga");
    $contents = $template->getContents();
    $this->assertEquals("fuga/fuga.tpl", rtrim($contents));
  }
  
  public function testIsValid()
  {
    $this->assertTrue(self::$repository->isValid("controller", "index"));
    $this->assertTrue(self::$repository->isValid("controller", "fuga"));
    
    $this->assertTrue(self::$repository->isValid("module", "error"));
    $this->assertFalse(self::$repository->isValid("module", "fuga"));
    
    $this->assertTrue(self::$repository->isValid("app", "serverError"));
    $this->assertFalse(self::$repository->isValid("app", "error"));
    $this->assertFalse(self::$repository->isValid("app", "index"));
    
    $threw = false;
    try {
      self::$repository->isValid("hoge", "index");
    } catch (Exception $e) {
      $threw = true;
    }
    
    $this->assertTrue($threw);
  }
  
  public function testCreate()
  {
    $time = microtime();
    self::$repository->create("controller", "new", $time);
    $this->assertEquals($time, trim(self::$repository->getContents("new")));
  }
  
  public function testDelete()
  {
    self::$repository->delete("controller", "new");
    $this->assertNull(self::$repository->getValidTemplate("new"));
  }
  
  private function createRepository($controllerName)
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
