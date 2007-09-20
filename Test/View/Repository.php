<?php

class Test_View_Repository extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_View_Repository");
  }
  
  public function testRepositoryTemplateFile()
  {
    $module = "index";
    
    $destination = new Sabel_Destination($module, "index", "index");
    $repository  = new Sabel_View_Repository_File($destination);
        
    $resource = $repository->find();
    $this->assertFalse($resource->isMissing());
    $this->assertFalse($repository->find("test"));
  }
  
  public function testRepositoryTemplateFilePath()
  {
    $module = "index";
    
    $destination = new Sabel_Destination($module, "index", "index");
    $repository = new Sabel_View_Repository_File_Expose($destination);
    
    $basedir = RUN_BASE . "/app/{$module}/";
    $this->assertEquals($basedir, $repository->getPathToBaseDirectory($module));
    
    $resource = $repository->find();
    $this->assertEquals(RUN_BASE . "/app/index/views/index/", $resource->getPath());
    $this->assertEquals("index.tpl", $resource->getName());
  }
  
  public function testCreateResource()
  {
    $destination = new Sabel_Destination("index", "index", "test");
    
    $repository = new Sabel_View_Repository_File($destination);
    $resource = $repository->find();
    $this->assertFalse($resource);
    
    $repository->createResource("leaf", "template");
    $resource = $repository->find();
    $this->assertFalse($resource->isMissing());
  }
}

class Sabel_View_Repository_File_Expose extends Sabel_View_Repository_File
{
  public function getPathToBaseDirectory($module)
  {
    return parent::getPathToBaseDirectory($module);
  }
}