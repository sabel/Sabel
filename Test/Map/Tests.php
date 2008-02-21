<?php

require_once ("Test/Map/Match.php");
require_once ("Test/Map/Destination.php");

class Test_Map_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Map_Match::suite());
    $suite->addTest(Test_Map_Destination::suite());
    
    return $suite;
  }
}
