<?php

require_once ("Test/Request/Object.php");
require_once ("Test/Request/Uri.php");
require_once ("Test/Request/Token.php");

class Test_Request_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Request_Object::suite());
    $suite->addTest(Test_Request_Uri::suite());
    $suite->addTest(Test_Request_Token::suite());
    
    return $suite;
  }
}
