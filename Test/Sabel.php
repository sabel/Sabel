<?php

require ("generator/generator.php");
 
/**
 * TestCase for Sabel Aplication
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Sabel extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Sabel");
  }
  
  public function __construct()
  {
    if (is_dir(RUN_BASE)) {
      $dt = new Sabel_Util_DirectoryTraverser(RUN_BASE);
      $remover = new SabelDirectoryAndFileRemover();
      $dt->visit($remover);
      $dt->traverse();
      $remover->removeEmptyDirectories();
      rmdir(RUN_BASE);
    }
    
    if (!is_dir(RUN_BASE)) {
      mkdir(RUN_BASE);
      $dt = new Sabel_Util_DirectoryTraverser(SABEL_BASE . "/generator/skeleton");
      $dt->visit(new SabelDirectoryAndFileCreator());
      $dt->traverse();
    }
  }
  
  public function testSabel()
  {
    set_include_path(get_include_path() . ":" . RUN_BASE . "/app/");
    
    require (RUN_BASE . "/config/environment.php");
    require (RUN_BASE . "/config/Factory.php");
    require (RUN_BASE . "/config/connection.php");
    
    $request = new Sabel_Request_Object();
    $request->get("index/index");
    
    $storage = new Sabel_Storage_InMemory();
    
    $bus = new Sabel_Bus();
    $bus->set("request", $request);
    $bus->set("storage", $storage);
    
    $bus->addProcessor(new Sabel_Processor_Request("request"));
    $bus->addProcessor(new Sabel_Processor_Router("router"));
    $bus->addProcessor(new Sabel_Processor_Helper("helper"));
    $bus->addProcessor(new Sabel_Processor_Creator("creator"));
    $bus->addProcessor(new Sabel_Processor_Executer("executer"));
    $bus->addProcessor(new Sabel_Processor_Response("response"));
    $bus->addProcessor(new Sabel_Processor_Renderer("renderer"));
    
    $result = $bus->run();
    
    $this->assertTrue(is_string($result));
  }
}
