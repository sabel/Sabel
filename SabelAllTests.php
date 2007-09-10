<?php

define("SABEL_BASE",  dirname(realpath(__FILE__)));
define("RUN_BASE",    SABEL_BASE . '/Test/data/application');
define("TEST_CASE",   true);
define("PRODUCTION",  0x01);
define("TEST",        0x05);
define("DEVELOPMENT", 0x0A);
define("ENVIRONMENT", DEVELOPMENT);

define("__TRUE__",  "true");
define("__FALSE__", "false");

error_reporting(E_ALL|E_STRICT);
ini_set('memory_limit', '32m');

if (!defined('PHPUnit_MAIN_METHOD')) {
  define('PHPUnit_MAIN_METHOD', 'SabelAllTests::main');
}

define ("PHPUNIT_VERSION", 3);

switch (PHPUNIT_VERSION) {
  case 2:
    require_once("PHPUnit2/Framework/Test.php");
    require_once("PHPUnit2/Framework/Warning.php");
    require_once("PHPUnit2/TextUI/TestRunner.php");
    require_once("PHPUnit2/Framework/TestCase.php");
    require_once("PHPUnit2/Framework/TestSuite.php");
    require_once("PHPUnit2/Framework/IncompleteTestError.php");
    break;
  case 3:
    require_once("PHPUnit/Framework/Test.php");
    require_once("PHPUnit/Framework/Warning.php");
    require_once("PHPUnit/TextUI/TestRunner.php");
    require_once("PHPUnit/Framework/TestCase.php");
    require_once("PHPUnit/Framework/TestSuite.php");
    require_once("PHPUnit/Framework/IncompleteTestError.php");
    break;
}

require_once('Sabel.php');
require_once('Test/SabelTestCase.php');
require_once('Test/SabelTestSuite.php');
require_once('Test/Sabel.php');
require_once('Test/Bus.php');
require_once('Test/Annotation.php');
require_once('Test/Aspect.php');
require_once('Test/Container.php');
require_once('Test/Pager.php');
require_once('Test/PageViewer.php');
require_once('Test/Parameters.php');
require_once('Test/Request.php');
require_once('Test/Resolver.php');
require_once('Test/Experimental.php');

// require_once('Test/Cache.php');
require_once('Test/Util.php');
require_once('Test/String.php');
require_once('Test/UtilMap.php');

require_once('Test/DB/Tests.php');
require_once('Test/Validate.php');
require_once('Test/Map/Tests.php');

require_once('Test/VirtualInheritance.php');

class SabelAllTests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    if (PHPUNIT_VERSION === 2) {
      $suite = new PHPUnit2_Framework_TestSuite();
    } elseif (PHPUNIT_VERSION === 3) {
      $suite = new PHPUnit_Framework_TestSuite();
    }
    
    $suite->addTest(Test_Sabel::suite());
    $suite->addTest(Test_DB_Tests::suite());
    $suite->addTest(Test_Bus::suite());
    // $suite->addTest(Test_Map_Tests::suite());
    // $suite->addTest(Test_Validate::suite());
    $suite->addTest(Test_Annotation::suite());
    $suite->addTest(Test_Pager::suite());
    $suite->addTest(Test_PageViewer::suite());
    $suite->addTest(Test_Request::suite());
    $suite->addTest(Test_Parameters::suite());
    $suite->addTest(Test_Container::suite());
    $suite->addTest(Test_Util::suite());
    $suite->addTest(Test_String::suite());
    $suite->addTest(Test_UtilMap::suite());
    $suite->addTest(Test_Experimental::suite());
    
    return $suite;
    
    /*
    $suite->addTest(Test_Aspect::suite());
    $suite->addTest(Test_Container::suite());
    $suite->addTest(Test_Cache::suite());
    $suite->addTest(Test_VirtualInheritance::suite());
    */
  }
}
