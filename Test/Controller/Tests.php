<?php

require_once ('Test/Controller/Page.php');

class Test_Controller_Tests extends SabelTestSuite
{
  public static function suite()
  {
    $suite = self::createSuite();
    
    $suite->addTest(Test_Controller_Page::suite());
    
    return $suite;
  }
}
