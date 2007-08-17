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
  
  public function testBusProcessor()
  {
    $bus = new Sabel_Bus();
    
    $bus->addProcessor(new Test_Bus_Processor("test"));
    $bus->run();
    
    $this->assertEquals("test", $bus->get("test"));
  }
  
  public function testBusInit()
  {
    $bus = new Sabel_Bus();
    $bus->init(array("null"   => null,
                     "int"    => 10,
                     "string" => "test",
                     "bool"   => false));
    
    $this->assertEquals(null,   $bus->get("null"));
    $this->assertEquals(10,     $bus->get("int"));
    $this->assertEquals("test", $bus->get("string"));
    $this->assertEquals(false,  $bus->get("bool"));
  }
  
  public function testAddAndGetBusGroup()
  {
    $request  = "request";
    $executer = "executer";
    
    $bus = new Sabel_Bus();
    
    $bus->addProcessor(new Test_Bus_Processor($request));
    $bus->addProcessor(new Test_Bus_Processor($executer));
    
    $this->assertTrue(is_object($bus->getProcessor($request)));
    $this->assertTrue(is_object($bus->getProcessor($executer)));
  }
  
  public function testBusGroupInsertPrevious()
  {
    $group = new Sabel_Bus_ProcessorGroup("test");
    $this->assertTrue(is_object($group));
  }
  
  public function testBusGroupInsertNext()
  {
    
  }
  
  public function testBusGroupSequential()
  {
    $bus = new Sabel_Bus_ProcessorGroup("test");
  }
  
  public function testBusController()
  {
    $bus = new Sabel_Bus();
    
    $bg = new Sabel_Bus_ProcessorGroup("r0");
    $bg->add(new RequestProcessor("r1"));
    $bg->get("r1")->insertNext(new RequestProcessor("r2"));
    
    $controller = new Controller();
    $bg->addController($controller);
    
    $bg->execute($bus);
    
    $this->assertEquals(array("r1", "r2"), $controller->results);
  }
  
  /**
   * バスコントローラはプロセッサをスキップすることができる
   */
  public function testBusControllerSkip()
  {
    
  }
  
  public function testListSize()
  {
    $list = new Sabel_Bus_ProcessorList(new Test_Bus_Processor("r1"));
    
    $last = $list->insertNext(new Test_Bus_Processor("r2"));
    $this->assertEquals(2, $list->size());
    
    $last = $last->insertNext(new Test_Bus_Processor("r3"));
    $this->assertEquals(3, $list->size());
    
    $last->previous->unlinkNext();
    $this->assertEquals(2, $list->size());
  }
  
  public function testBusEvent()
  {
    $bus = new Sabel_Bus();
    
  }
}

class Test_Bus_Processor extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $bus->set("test", "test");
  }
}

class Controller extends Sabel_Bus_Controller
{
  public $results = array();
  
  public function execute($processor, $bus)
  {
    $this->results[] = $processor->name;
    return true;
  }
}

class RequestProcessor extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    //
  }
}
class RequestProcessorTwo extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    //
  }
}