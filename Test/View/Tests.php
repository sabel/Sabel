<?php

if (!defined("VIEW_DIR_NAME")) define("VIEW_DIR_NAME", "views");
if (!defined("TPL_SUFFIX")) define("TPL_SUFFIX", ".tpl");
if (!defined("DS")) define("DS", DIRECTORY_SEPARATOR);

/**
 * @category  View
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Test_View_Tests extends SabelTestCase
{
  protected static $repository = null;
  
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
}
