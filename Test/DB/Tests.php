<?php

require_once('Test/DB/Test.php');
require_once('Test/DB/Mysql.php');
require_once('Test/DB/Pgsql.php');
require_once('Test/DB/SQLite.php');

class Test_DB_Tests
{
  public static function main()
  {
    PHPUnit2_TextUI_TestRunner::run(self::suite());
  }

  public static function suite()
  {
    $suite = new PHPUnit2_Framework_TestSuite('map all tests');
    
    $suite->addTest(Test_DB_Mysql::suite());
    $suite->addTest(Test_DB_Pgsql::suite());
    $suite->addTest(Test_DB_SQLite::suite());
    $suite->addTest(Test_DB_InformationSchema::suite());
    
    return $suite;
  }
}