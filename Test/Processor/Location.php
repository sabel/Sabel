<?php

/**
 * testcase for lib.processor.Location
 *
 * using classes:
 *   sabel.view.Repository, sabel.view.template.File
 *   sabel.map.Destination, sabel.controller.Page, sabel.response.Object
 *
 * @category  Processor
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Processor_Location extends Test_Processor_Abstract
{
  public static function suite()
  {
    Sabel::fileUsing(PROCESSORS_DIR . DS . "Location.php", true);
    return self::createSuite("Test_Processor_Location");
  }
  
  public function testProcess()
  {
    $bus = $this->bus;
    $bus->set("destination", $this->getDestination());
    $bus->set("controller", new TestIndexController(new Sabel_Response_Object()));
    
    $processor = new Processor_Location("location");
    $processor->execute($bus);
    
    $view = $bus->get("view");
    $this->assertTrue($view->getTemplate("app") instanceof Sabel_View_Template);
    $this->assertTrue($view->getTemplate("module") instanceof Sabel_View_Template);
    $this->assertTrue($view->getTemplate("controller") instanceof Sabel_View_Template);
    $this->assertNull($view->getTemplate("hoge"));
  }
  
  protected function getDestination()
  {
    return new Sabel_Map_Destination(array("module"     => "Index",
                                           "controller" => "index",
                                           "action"     => "index"));
  }
}

class TestIndexController extends Sabel_Controller_Page {}
