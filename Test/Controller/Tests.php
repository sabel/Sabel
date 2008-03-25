<?php

require_once ("Test/Controller/Page.php");
require_once ("Test/Controller/Redirector.php");

class Test_Controller_Tests
{
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    $suite->addTest(Test_Controller_Page::suite());
    $suite->addTest(Test_Controller_Redirector::suite());
    
    return $suite;
  }
}
