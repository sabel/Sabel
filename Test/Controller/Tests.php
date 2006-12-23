<?php

require_once ('Test/Controller/Page.php');

class Test_Controller_Tests
{
  public static function main()
  {
    PHPUnit2_TextUI_TestRunner::run(self::suite());
  }

  public static function suite()
  {
    $suite = new PHPUnit2_Framework_TestSuite();
    
    $suite->addTest(Test_Controller_Page::suite());
    
    return $suite;
  }
}