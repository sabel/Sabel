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
    
    $request  = new Sabel_Bus_ProcessorGroup("request");
    $router   = new Sabel_Bus_ProcessorGroup("router");
    $helper   = new Sabel_Bus_ProcessorGroup("helper");
    $creator  = new Sabel_Bus_ProcessorGroup("creator");
    $executer = new Sabel_Bus_ProcessorGroup("executer");
    $response = new Sabel_Bus_ProcessorGroup("response");
    $renderer = new Sabel_Bus_ProcessorGroup("renderer");
    
    $request->add(new Sabel_Processor_Request("request"));
    $router->add(new Sabel_Processor_Router("router"));
    $helper->add(new Sabel_Processor_Helper("helper"));
    $creator->add(new Sabel_Processor_Creator("creator"));
    $executer->add(new Sabel_Processor_Executer("executer"));
    $response->add(new Sabel_Processor_Response("response"));
    $renderer->add(new Sabel_Processor_Renderer("renderer"));
    
    $bus->addProcessor($request);
    $bus->addProcessor($router);
    $bus->addProcessor($helper);
    $bus->addProcessor($creator);
    $bus->addProcessor($executer);
    $bus->addProcessor($response);
    $bus->addProcessor($renderer);
    
    $result = $bus->run();
    
    $this->assertTrue(is_string($result));
  }
}
