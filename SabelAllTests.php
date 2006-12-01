<?php

define('SABEL_BASE', dirname(realpath(__FILE__)));
define('RUN_BASE', SABEL_BASE . '/Test/data/application');
define('TEST_CASE', true);
define('ENVIRONMENT', 'development');

error_reporting(E_ALL|E_STRICT);
ini_set('memory_limit', '16m');

if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'SabelAllTests::main');
}

require_once('PHPUnit2/Framework/Test.php');
require_once('PHPUnit2/Framework/Warning.php');
require_once('PHPUnit2/TextUI/TestRunner.php');
require_once('PHPUnit2/Framework/TestCase.php');
require_once('PHPUnit2/Framework/TestSuite.php');
require_once('PHPUnit2/Framework/IncompleteTestError.php');

require_once('Sabel.php');

function __autoload($class)
{
  Sabel::using($class);
}

Sabel::fileUsing('sabel/Functions.php');
Sabel::fileUsing('sabel/db/Functions.php');

require_once('Test/SabelTestCase.php');
require_once('Test/Sabel.php');
require_once('Test/Annotation.php');
require_once('Test/Aspect.php');
require_once('Test/DI.php');
require_once('Test/Pager.php');
require_once('Test/PageViewer.php');
require_once('Test/Parameters.php');
require_once('Test/Request.php');
require_once('Test/RequestUri.php');
require_once('Test/Resolver.php');

// require_once('Test/Container.php');
require_once('Test/Classes.php');
// require_once('Test/Cache.php');

require_once('Test/DB/Tests.php');
require_once('Test/Map/Tests.php');

require_once('Test/Namespace.php');
require_once('Test/Validate.php');
require_once('Test/VirtualInheritance.php');

require_once('Test/Form.php');

class SabelAllTests
{
  public static function main()
  {
    PHPUnit2_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite()
  {
    $suite = new PHPUnit2_Framework_TestSuite();
    
    $suite->addTest(Test_Sabel::suite());
    // $suite->addTest(Test_Annotation::suite());
    // $suite->addTest(Test_DI::suite());
    // $suite->addTest(Test_Aspect::suite());
    
    // $suite->addTest(Test_Resolver::suite());
    // $suite->addTest(Test_Container::suite());
    // $suite->addTest(Test_Classes::suite());
    // $suite->addTest(Test_Cache::suite());
    
    $suite->addTest(Test_Pager::suite());
    $suite->addTest(Test_PageViewer::suite());
    
    $suite->addTest(Test_Request::suite());
    $suite->addTest(Test_Parameters::suite());
    $suite->addTest(Test_RequestUri::suite());
    
    $suite->addTest(Test_Namespace::suite());
    
    $suite->addTest(Test_DB_Tests::suite());
    $suite->addTest(Test_Map_Tests::suite());
    
    //$suite->addTest(Test_Validate::suite());
    // $suite->addTest(Test_VirtualInheritance::suite());
    
    $suite->addTest(Test_Form::suite());
    
    return $suite;
  }
}

if (PHPUnit2_MAIN_METHOD == 'SabelAllTests::main') {
  SabelAllTests::main();
}