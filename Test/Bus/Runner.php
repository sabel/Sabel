<?php
 
/**
 * a test case of sabel.Bus, sabel.bus.*
 *
 * @category  Bus
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Bus_Runner extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Bus_Runner");
  }
  
  public function testInstanceOf()
  {
    $config = new TestBusConfig();
    $bus = $config->configure()->getBus();
    $this->assertTrue($bus instanceof Sabel_Bus);
  }
  
  public function testConfigure()
  {
    $config = new TestBusConfig();
    $bus = $config->configure()->getBus();
    $list = $bus->getProcessorList();
    $this->assertTrue($list->has("hoge"));
    $this->assertTrue($list->has("fuga"));
    $this->assertTrue($list->has("foo"));
    $this->assertFalse($list->has("bar"));
  }
  
  public function testBusInit()
  {
    $config = new TestBusConfig();
    $bus = $config->configure()->getBus();
    $bus->init(array("null"   => null,
                     "int"    => 10,
                     "string" => "test",
                     "bool"   => false));
    
    $this->assertEquals(null,   $bus->get("null"));
    $this->assertEquals(10,     $bus->get("int"));
    $this->assertEquals("test", $bus->get("string"));
    $this->assertEquals(false,  $bus->get("bool"));
  }
  
  public function testRun()
  {
    $config = new TestBusConfig();
    $bus = $config->configure()->getBus();
    $bus->run();
    
    $this->assertEquals("10", $bus->get("a"));
    $this->assertEquals("20", $bus->get("b"));
    $this->assertEquals(null, $bus->get("c"));
  }
  
  public function testAttatchExecuteBeforeEvent()
  {
    $config = new TestBusConfig();
    $bus = $config->configure()->getBus();
    $bus->attachExecuteBeforeEvent("foo", new TestEvent(), "beforeMethod");
    $bus->run();
    
    $this->assertEquals("before: fuga_result", $bus->get("beforeResult"));
  }
  
  public function testAttatchExecuteAfterEvent()
  {
    $config = new TestBusConfig();
    $bus = $config->configure()->getBus();
    $bus->attachExecuteAfterEvent("hoge", new TestEvent(), "afterMethod");
    $bus->run();
    
    $this->assertEquals("after: hoge_result", $bus->get("afterResult"));
  }
  
  public function testHasMethod()
  {
    $config = new TestBusConfig();
    $bus = $config->configure()->getBus();
    $bus->set("a", "10");
    $bus->set("b", "20");
    $bus->set("c", "30");
    
    $this->assertTrue($bus->has("a"));
    $this->assertFalse($bus->has("d"));
    
    $this->assertTrue($bus->has(array("a", "b", "c")));
    $this->assertFalse($bus->has(array("a", "d", "c")));
  }
}

class TestEvent
{
  public function beforeMethod($bus)
  {
    $bus->set("beforeResult", "before: " . $bus->get("result"));
  }
  
  public function afterMethod($bus)
  {
    $bus->set("afterResult", "after: " . $bus->get("result"));
  }
}

class TestBusConfig extends Sabel_Bus_Config
{
  public function configure()
  {
    $processors = array("hoge", "fuga", "foo");
    
    $bus = $this->bus;
    foreach ($processors as $name) {
      $processor = ucfirst($name);
      $className = "Processor_" . $processor;
      $bus->addProcessor(new $className($name));
    }
    
    return $this;
  }
}

class Processor_Hoge extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $bus->set("a", "10");
    $bus->set("result", "hoge_result");
  }
}

class Processor_Fuga extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $bus->set("b", "20");
    $bus->set("result", "fuga_result");
  }
}

class Processor_Foo extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $bus->set("result", "foo_result");
  }
}
