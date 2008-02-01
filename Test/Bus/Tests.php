<?php

require_once ("Test/Bus/Runner.php");

class Test_Bus_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Bus_Runner::suite());
    
    return $suite;
  }
}
