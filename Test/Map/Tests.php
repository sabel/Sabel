<?php

require_once ('Test/Map/Usage.php');
require_once ('Test/Map/Candidate.php');

class Test_Map_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Map_Usage::suite());
    $suite->addTest(Test_Map_Candidate::suite());
    
    return $suite;
  }
}
