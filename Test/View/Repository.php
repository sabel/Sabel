<?php

class Test_View_Repository extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_View_Repository");
  }
  
  public function testRepository()
  {
    $repository = new Sabel_View_Repository();
    $repository->add("module", "controller", "action");
    
    $resource = $repository->get("module", "controller", "action");
  }
  
  public function testRepositoryTemplateFile()
  {
    $repository = new Sabel_View_Repository_File();
    $module = "index";
    
    $basedir = RUN_BASE . "/app/{$module}/";
    $this->assertEquals($basedir, $repository->getPathToBaseDirectory($module));
    
    $resource = $repository->get($module, "index", "index");
    $this->assertEquals(RUN_BASE . "/app/index/views/index/", $resource->getPath());
    $this->assertEquals("index.tpl", $resource->getName());
    $this->assertFalse($resource->isMissing());
    
    $resource2 = $repository->get("index", "index", "test");
    $this->assertTrue($resource2->isMissing());
  }
  
  public function testCreateResource()
  {
    $repository = new Sabel_View_Repository_File();
    $resource = $repository->get("index", "index", "test");
    $this->assertTrue($resource->isMissing());
    
    $repository->createResource("index", "index", "test", "template");
    $resource = $repository->get("index", "index", "test");
    $this->assertFalse($resource->isMissing());
  }
}
