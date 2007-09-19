<?php

// require_once ('Test/View/View.php');
// require_once ('Test/View/Locator.php');
require_once ('Test/View/Repository.php');

class Test_View_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_View_Repository::suite());
    
    return $suite;
  }
}
