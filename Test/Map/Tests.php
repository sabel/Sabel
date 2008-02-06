<?php

require_once ("Test/Map/Match.php");
require_once ("Test/Map/Destination.php");
require_once ("Test/Map/Elements.php");

class Test_Map_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Map_Match::suite());
    $suite->addTest(Test_Map_Destination::suite());
    $suite->addTest(Test_Map_Elements::suite());
    
    return $suite;
  }
}
