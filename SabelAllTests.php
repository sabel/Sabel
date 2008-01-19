<?php

define("SABEL_BASE",  dirname(realpath(__FILE__)));
define("RUN_BASE",    SABEL_BASE . "/Test/data/application");
define("TEST_CASE",   true);
define("PRODUCTION",  0x01);
define("TEST",        0x05);
define("DEVELOPMENT", 0x0A);
define("ENVIRONMENT", TEST);
define("PHP_SUFFIX",  ".php");

error_reporting(E_ALL|E_STRICT);

if (!defined("PHPUnit_MAIN_METHOD")) {
  define("PHPUnit_MAIN_METHOD", "SabelAllTests::main");
}

require_once("PHPUnit/Framework/Test.php");
require_once("PHPUnit/Framework/Warning.php");
require_once("PHPUnit/TextUI/TestRunner.php");
require_once("PHPUnit/Framework/TestCase.php");
require_once("PHPUnit/Framework/TestSuite.php");
require_once("PHPUnit/Framework/IncompleteTestError.php");

require_once('Sabel.php');
require_once('Test/SabelTestCase.php');
require_once('Test/SabelTestSuite.php');
require_once('Test/Bus.php');
require_once('Test/Object.php');
require_once('Test/Annotation.php');
require_once('Test/Aspect.php');
require_once('Test/Container.php');
require_once('Test/Pager.php');
require_once('Test/PageViewer.php');
require_once('Test/Parameters.php');
require_once('Test/Request.php');
require_once('Test/Destination.php');
require_once('Test/View/TemplateFile.php');
require_once('Test/Logger/File.php');
require_once('Test/Storage/InMemory.php');
require_once('Test/Cache/Tests.php');

require_once('Test/Util/String.php');
require_once('Test/Util/List.php');
require_once('Test/Util/Map.php');

require_once('Test/DB/Tests.php');
require_once('Test/Validate.php');
require_once('Test/Map/Tests.php');
//require_once('Test/Processor/Tests.php');

require_once('Test/VirtualInheritance.php');
require_once('Test/Reflection.php');

class SabelAllTests
{
  public static function main()
  {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit_Framework_TestSuite();
    
    //$suite->addTest(Test_DB_Tests::suite());
    $suite->addTest(Test_Validate::suite());
    
    $suite->addTest(Test_Bus::suite());
    $suite->addTest(Test_Object::suite());
    $suite->addTest(Test_Map_Tests::suite());
    $suite->addTest(Test_Annotation::suite());
    $suite->addTest(Test_Reflection::suite());
    $suite->addTest(Test_Pager::suite());
    $suite->addTest(Test_PageViewer::suite());
    $suite->addTest(Test_Request::suite());
    $suite->addTest(Test_Parameters::suite());
    $suite->addTest(Test_Container::suite());
    $suite->addTest(Test_Destination::suite());
    $suite->addTest(Test_View_TemplateFile::suite());
    $suite->addTest(Test_Logger_File::suite());
    $suite->addTest(Test_Util_String::suite());
    $suite->addTest(Test_Util_List::suite());
    $suite->addTest(Test_Util_Map::suite());
    $suite->addTest(Test_Storage_InMemory::suite());
    $suite->addTest(Test_Cache_Tests::suite());
    
    // $suite->addTest(Test_Processor_Tests::suite());
    $suite->addTest(Test_VirtualInheritance::suite());
    // $suite->addTest(Test_Aspect::suite());
    
    return $suite;
  }
}
