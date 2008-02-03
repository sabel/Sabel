<?php

define("SABEL_BASE", dirname(realpath(__FILE__)));
define("DS", DIRECTORY_SEPARATOR);
define("RUN_BASE", SABEL_BASE . DS . "Test" . DS . "data" . DS . "application");

PHPUnit_Util_Filter::addDirectoryToFilter(SABEL_BASE . DS . "Test");
PHPUnit_Util_Filter::addFileToFilter(SABEL_BASE . DS . "SabelAllTests.php");

define("TEST_CASE",   true);
define("PRODUCTION",  0x01);
define("TEST",        0x05);
define("DEVELOPMENT", 0x0A);
define("ENVIRONMENT", TEST);

define("PHP_SUFFIX", ".php");
define("IS_WIN", DIRECTORY_SEPARATOR === '\\');

error_reporting(E_ALL|E_STRICT);

if (!defined("PHPUnit_MAIN_METHOD")) {
  define("PHPUnit_MAIN_METHOD", "SabelAllTests::main");
}

require_once ("PHPUnit/Framework/Test.php");
require_once ("PHPUnit/Framework/Warning.php");
require_once ("PHPUnit/TextUI/TestRunner.php");
require_once ("PHPUnit/Framework/TestCase.php");
require_once ("PHPUnit/Framework/TestSuite.php");
require_once ("PHPUnit/Framework/IncompleteTestError.php");

require_once ("Sabel.php");
require_once ("Test/SabelTestCase.php");
require_once ("Test/SabelTestSuite.php");
require_once ("Test/Object.php");
require_once ("Test/Environment.php");
require_once ("Test/Annotation.php");
require_once ("Test/Aspect.php");
require_once ("Test/Container.php");
require_once ("Test/Exception.php");
require_once ("Test/Bus/Tests.php");
require_once ("Test/Request/Tests.php");
require_once ("Test/Response/Tests.php");
require_once ("Test/Controller/Tests.php");
require_once ("Test/Util/Tests.php");
require_once ("Test/View/Tests.php");
require_once ("Test/Processor/Tests.php");
require_once ("Test/Reflection.php");
require_once ("Test/Storage/InMemory.php");
require_once ("Test/Cache/Tests.php");
require_once ("Test/Map/Tests.php");
require_once ("Test/VirtualInheritance.php");
require_once ("Test/DB/Tests.php");
require_once ("Test/Locale/Browser.php");
require_once ("Test/Locale/Server.php");
require_once ("Test/I18n/Sabel.php");
require_once ("Test/I18n/PhpGettext.php");

class SabelAllTests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    $suite->addTest(Test_Object::suite());
    $suite->addTest(Test_Environment::suite());
    $suite->addTest(Test_Bus_Tests::suite());
    $suite->addTest(Test_Map_Tests::suite());
    $suite->addTest(Test_Request_Tests::suite());
    $suite->addTest(Test_Response_Tests::suite());
    $suite->addTest(Test_Controller_Tests::suite());
    $suite->addTest(Test_Util_Tests::suite());
    $suite->addTest(Test_View_Tests::suite());
    $suite->addTest(Test_Cache_Tests::suite());
    $suite->addTest(Test_Processor_Tests::suite());
    
    $suite->addTest(Test_Annotation::suite());
    $suite->addTest(Test_Reflection::suite());
    $suite->addTest(Test_Container::suite());
    $suite->addTest(Test_Exception::suite());
    
    $suite->addTest(Test_Storage_InMemory::suite());
    $suite->addTest(Test_VirtualInheritance::suite());
    $suite->addTest(Test_Locale_Browser::suite());
    $suite->addTest(Test_Locale_Server::suite());
    $suite->addTest(Test_I18n_Sabel::suite());
    $suite->addTest(Test_I18n_PhpGettext::suite());
    // $suite->addTest(Test_DB_Tests::suite());
    
    // $suite->addTest(Test_Aspect::suite());
    
    return $suite;
  }
}
