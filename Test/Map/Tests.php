<?php

require_once ('Test/Map/Entry.php');
require_once ('Test/Map/Destination.php');
require_once ('Test/Map/Uri.php');
require_once ('Test/Map/Requirements.php');
require_once ('Test/Map/Builder.php');

require_once ('Test/Map/Usage.php');
require_once ('Test/Map/Selecter.php');
require_once ('Test/Map/Candidate.php');

class Test_Map_Tests
{
  public static function main()
  {
    PHPUnit2_TextUI_TestRunner::run(self::suite());
  }

  public static function suite()
  {
    $suite = new PHPUnit2_Framework_TestSuite();
    
    $suite->addTest(Test_Map_Usage::suite());
    $suite->addTest(Test_Map_Selecter::suite());
    $suite->addTest(Test_Map_Candidate::suite());
    
    return $suite;
  }
}