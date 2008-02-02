<?php

/**
 * testcase for lib.processor.Controller
 * using classes: sabel.map.Destination, sabel.controller.Page
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Controller extends Test_Processor_Abstract
{
  public static function suite()
  {
    Sabel::fileUsing(PROCESSORS_DIR . DS . "Controller.php", true);
    return self::createSuite("Test_Processor_Controller");
  }
  
  public function testHogeController()
  {
    $bus = $this->bus;
    $bus->set("destination", $this->getDestination("Hoge"));
    
    $processor = new Processor_Controller("controller");
    $processor->setBus($bus);
    $processor->execute($bus);
    
    $this->assertTrue($bus->get("controller") instanceof Test_Controllers_Hoge);
    $this->assertTrue($bus->get("response") instanceof Sabel_Response);
  }
  
  public function testFugaController()
  {
    $bus = $this->bus;
    $bus->set("destination", $this->getDestination("Fuga"));
    
    $processor = new Processor_Controller("controller");
    $processor->setBus($bus);
    $processor->execute($bus);
    
    $this->assertTrue($bus->get("controller") instanceof Test_Controllers_Fuga);
  }
  
  public function testCannotCreateController()
  {
    $bus = $this->bus;
    $bus->set("destination", $this->getDestination("Abcde"));
    
    $processor = new Processor_Controller("controller");
    $processor->setBus($bus);
    
    try {
      $processor->execute($bus);
    } catch (Sabel_Exception_Runtime $e) {
      return;
    }
    
    $this->fail();
  }
  
  protected function getDestination($name)
  {
    return new Sabel_Map_Destination(array("module"     => "Test",
                                           "controller" => $name,
                                           "action"     => "index"));
  }
}

class Test_Controllers_Hoge extends Sabel_Controller_Page {}
class Test_Controllers_Fuga extends Sabel_Controller_Page {}
