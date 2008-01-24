<?php

/**
 * @category  Router
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Test_Destination extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Destination");
  }
  
  public function testDestination()
  {
    $destination = new Sabel_Destination("module", "controller", "action");
    $this->assertEquals("module", $destination->getModule());
    $this->assertEquals("controller", $destination->getController());
    $this->assertEquals("action", $destination->getAction());
  }
  
  public function testModule()
  {
    $destination = new Sabel_Destination("module", "controller", "action");
    $this->assertTrue($destination->hasModule());
    $destination->setModule("admin");
    $this->assertEquals("admin", $destination->getModule());
  }
  
  public function testController()
  {
    $destination = new Sabel_Destination("module", "controller", "action");
    $this->assertTrue($destination->hasController());
    $destination->setController("main");
    $this->assertEquals("main", $destination->getController());
  }
  
  public function testAction()
  {
    $destination = new Sabel_Destination("module", "controller", "action");
    $this->assertTrue($destination->hasAction());
    $destination->setAction("index");
    $this->assertEquals("index", $destination->getAction());
  }
  
  public function testToArray()
  {
    $destination = new Sabel_Destination("module", "controller", "action");
    list ($m, $c, $a) = $destination->toArray();
    $this->assertEquals("module", $m);
    $this->assertEquals("controller", $c);
    $this->assertEquals("action", $a);
  }
}
