<?php

require_once ("Test/Response/Object.php");
require_once ("Test/Response/Header.php");

class Test_Response_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Response_Object::suite());
    $suite->addTest(Test_Response_Header::suite());
    
    return $suite;
  }
}
