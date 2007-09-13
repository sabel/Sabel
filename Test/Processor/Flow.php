<?php

require_once ("generator/skeleton/lib/processor/Flow.php");
require_once ("Test/Processor/classes/Index.php");

/**
 * TestCase of Processor_Flow
 *
 * @category   Test
 * @package    test.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Processor_Flow extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Processor_Flow");
  }
  
  private $bus = null;
  
  public function setUp()
  {
    $bus = new Sabel_Bus();
    
    // @todo "index/index" とかないときにえらるよ。
    // Fatal error: Call to a member function getType() on a non-object in 
    // /Users/morireo/Repository/sabel/sabel/request/Object.php on line 333
    $request     = new Sabel_Request_Object("index/index");
    
    $storage     = new Sabel_Storage_InMemory();
    $controller  = new Index();
    $destination = new Sabel_Destination("index", "index", "top");
    
    $controller->setup($request, $destination, $storage);
    
    $bus->set("request",     $request);
    $bus->set("storage",     $storage);
    $bus->set("controller",  $controller);
    $bus->set("destination", $destination);
    
    $this->bus = $bus;
  }
  
  public function testStandardFlow()
  {
    $processor = new Processor_Flow("flow");
    $processor->execute($this->bus);
  }
  
  public function testFailRequired()
  {
    $bus = new Sabel_Bus();
    $processor = new Processor_Flow("flow");
    
    try {
      $processor->execute($bus);
      $this->fail();
    } catch (Sabel_Exception_Runtime $e) {
      $this->assertTrue(true);
    }
  }
}
