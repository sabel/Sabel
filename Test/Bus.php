<?php
 
/**
 * a test case of Sabel Bus
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Bus extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Bus");
  }
  
  public function testBusStandard()
  {
    $bus = new Sabel_Bus();
    $bus->addProcessor("request", new RequestProcessor());
    $bus->addProcessor("choicer", new MapChoicer());
    $bus->addProcessor("ae", new ActionExecuter());
    $bus->process();
  }
  
  public function testBusComposit()
  {
    $mainBus = new Sabel_Bus();
    
    $input   = new Sabel_Bus();
    $input->addProcessor("request", new RequestProcessor());
    $input->addProcessor("choicer", new MapChoicer());
    
    $process = new Sabel_Bus();
    $process->addProcessor("ae", new ActionExecuter());
    
    $output  = new Sabel_Bus();
    
    $mainBus->addBus("input",   $input);
    $mainBus->addBus("process", $process);
    $mainBus->addBus("output",  $output);
    
    $mainBus->process();
  }
  
  public function testBusInterrupt()
  {
    $bus = new Sabel_Bus();
    $bus->addProcessor("some", new Wrapper(new RequestProcessor()));
  }
}

class Wrapper implements Sabel_Bus_Processor
{
  private $wrap = null;
  
  public function __construct($processor)
  {
    $this->wrap = $processor;
  }
  
  public function execute($bus)
  {
    return $this->wrap->execute($bus);
  }
}

class RequestProcessor implements Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request = new Sabel_Request_Object();
    $request->to("index/index");
    $bus->set("request", $request);
  }
}

class MapChoicer implements Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $sc = new Sabel_Context();
    $router = new Sabel_Router_Map();
    
    $request = $bus->get("request");
    $destination = $router->route($request, $sc);
    
    $bus->set("router", $router);
    $bus->set("destination", $destination);
    $bus->set("context", $sc);
  }
}

class ActionExecuter implements Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $array = $bus->get("request")->toArray();
    echo "execute action\n";
  }
}


class Map extends Sabel_Map_Config
{
  public function configure()
  {
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index"));
  }
}

