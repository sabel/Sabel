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
    
    $basedir = "/Users/morireo/Repository/sabel/Test/data/application/app/{$module}/";
    $this->assertEquals($basedir, $repository->getPathToBaseDirectory($module));
    
    $resource = $repository->get("index", "index", "index");
  }
}
