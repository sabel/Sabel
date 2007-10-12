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
  
  public function testListSize()
  {
    $list = new Sabel_Util_List("r1", new Test_Bus_Processor("r1"));
    
    $last = $list->insertNext("r2", new Test_Bus_Processor("r2"));
    $this->assertEquals(2, $list->size());
    
    $last = $last->insertNext("r3", new Test_Bus_Processor("r3"));
    $this->assertEquals(3, $list->size());
    
    $last->previous->unlinkNext();
    $this->assertEquals(2, $list->size());
  }
  
  public function testInsertPrevious()
  {
    $list = new Sabel_Util_List("r1", new Test_Bus_Processor("r1"));
    $list->insertPrevious("r2", new Test_Bus_Processor("r2"));
    $this->assertEquals(2, $list->size());
    $list->insertPrevious("r2", new Test_Bus_Processor("r2"));
    $this->assertEquals(3, $list->size());
  }
}

class Test_Bus_Processor extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $bus->set("test", "test");
  }
}

/*
class Controller extends Sabel_Bus_Controller
{
  public $results = array();
  
  public function execute($processor, $bus)
  {
    $this->results[] = $processor->name;
    return true;
  }
}
 */

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
