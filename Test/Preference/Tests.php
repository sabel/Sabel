<?php

require_once ("Test/Preference/Base.php");
require_once ("Test/Preference/Xml.php");

/**
 * test suite for Preference package
 *
 * @category  Preference
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Preference_Tests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    $suite->addTest(Test_Preference_Xml::suite());
    
    return $suite;
  }
}