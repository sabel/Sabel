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
    $basedir = "/Users/morireo/Repository/sabel/Test/data/application/app/index/";
    $this->assertEquals($basedir, $repository->getPathToBaseDirectory("index"));
    
    $resource = $repository->get("index", "index", "index");
  }
}
