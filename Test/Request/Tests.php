<?php

require_once ("Test/Request/Object.php");

class Test_Request_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Request_Object::suite());
    
    return $suite;
  }
}
