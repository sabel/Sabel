<?php

Sabel::using("Sabel_Request");
require_once ("MockRequest.php");
require_once ("PageControllerForTest.php");

class Test_Controller_Plugins extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Controller_Plugins");
  }
  
  public function testVolatile()
  {
    $aController = new PageControllerForTest();
    $aController->setup(new MockRequest());
    $session   = Sabel::load("Sabel_Storage_InMemory");
    $pVolatile = Sabel::load("Sabel_Controller_Plugin_Volatile", $session);
    $aController->registPlugin($pVolatile);
    $aController->execute("testVolatile");
    print_r($session);
    $aController->execute("testAction");
    $this->assertTrue(count($session->read("volatiles")) === 0);
  }
}