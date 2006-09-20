<?php

define('SABEL_BASE', dirname(realpath(__FILE__)));
define('RUN_BASE', SABEL_BASE . '/Test/data/application/');
define('TEST_CASE', true);
define('ENVIRONMENT', 'development');

error_reporting(E_ALL|E_STRICT);

if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'SabelAllTests::main');
}

require_once('PHPUnit2/Framework/Test.php');
require_once('PHPUnit2/Framework/Warning.php');
require_once('PHPUnit2/TextUI/TestRunner.php');
require_once('PHPUnit2/Framework/TestCase.php');
require_once('PHPUnit2/Framework/TestSuite.php');
require_once('PHPUnit2/Framework/IncompleteTestError.php');

require_once('Container.php');
$c  = new Container();
$dt = new DirectoryTraverser();
$dt->visit(new ClassCombinator(dirname(__FILE__).'/allclasses.php', null, false));
$dt->visit(new SabelClassRegister($c));
$dt->traverse();
require_once('allclasses.php');

require_once('Test/SabelTestCase.php');
require_once('Test/Sabel.php');
require_once('Test/Annotation.php');
require_once('Test/Aspect.php');
require_once('Test/DI.php');
require_once('Test/Pager.php');
require_once('Test/Map.php');
require_once('Test/Parameters.php');
require_once('Test/Request.php');
require_once('Test/RequestUri.php');
require_once('Test/Resolver.php');
require_once('Test/Edo_Test.php');
require_once('Test/Edo_Mysql.php');
require_once('Test/Edo_Pgsql.php');
require_once('Test/Edo_SQLite.php');
require_once('Test/InformationSchema.php');
require_once('Test/Container.php');
require_once('Test/Classes.php');

class SabelAllTests
{
  public static function main()
  {
    PHPUnit2_TextUI_TestRunner::run(self::suite());
  }

  public static function suite()
  {
    $suite = new PHPUnit2_Framework_TestSuite('sabel all tests');
    
    $suite->addTest(Test_Sabel::suite());
    $suite->addTest(Test_Annotation::suite());
    $suite->addTest(Test_DI::suite());
    $suite->addTest(Test_Aspect::suite());
    $suite->addTest(Test_Pager::suite());
    $suite->addTest(Test_Map::suite());
    $suite->addTest(Test_Parameters::suite());
    $suite->addTest(Test_Request::suite());
    $suite->addTest(Test_RequestUri::suite());
    $suite->addTest(Test_Resolver::suite());
    $suite->addTest(Test_Edo_Mysql::suite());
    $suite->addTest(Test_Edo_Pgsql::suite());
    // $suite->addTest(Test_Edo_SQLite::suite());
    $suite->addTest(Test_InformationSchema::suite());
    $suite->addTest(Test_Container::suite());
    $suite->addTest(Test_Classes::suite());
    
    return $suite;
  }
}

if (PHPUnit2_MAIN_METHOD == 'SabelAllTests::main') {
  SabelAllTests::main();
}
