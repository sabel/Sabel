<?php

require_once('Test/Map/Entry.php');
require_once('Test/Map/Destination.php');
require_once('Test/Map/Uri.php');
require_once('Test/Map/Requirements.php');
require_once('Test/Map/Builder.php');

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
    $suite->addTest(Test_Map_Uri::suite());
    // $suite->addTest(Test_Map_Requirements::suite());
    $suite->addTest(Test_Map_Builder::suite());
    
    return $suite;
  }
}