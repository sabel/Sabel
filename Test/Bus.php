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
    
    $bus->addProcessor("test", new Test_Bus_Processor());
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
    $bus = new Sabel_Bus();
    
    $bus->addGroup("request",  new Test_Bus_Processor());
    $bus->addGroup("executer", new Test_Bus_Processor());
    
    $this->assertTrue(is_object($bus->getGroup("request")));
    $this->assertTrue(is_object($bus->getGroup("executer")));
  }
  
  public function testBusGroupInsertPrevious()
  {
    $group = new Sabel_Bus_ProcessorGroup();
    $this->assertTrue(is_object($group));
  }
  
  public function testBusGroupInsertNext()
  {
    
  }
}

class Test_Bus_Processor implements Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $bus->set("test", "test");
  }
}
