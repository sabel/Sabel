<?php

require_once('Test/Map/Entry.php');
require_once('Test/Map/Destination.php');

class Test_Map_Tests
{
  public static function main()
  {
    PHPUnit2_TextUI_TestRunner::run(self::suite());
  }

  public static function suite()
  {
    $suite = new PHPUnit2_Framework_TestSuite('map all tests');
    
    $suite->addTest(Test_Map_Entry::suite());
    $suite->addTest(Test_Map_Destination::suite());
    
    return $suite;
  }
}