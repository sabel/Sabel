<?php

require_once('Test/DB/Test.php');
require_once('Test/DB/Mysql.php');
require_once('Test/DB/Pgsql.php');
require_once('Test/DB/SQLite.php');

class Test_DB_Tests
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

    if (extension_loaded('mysql') && extension_loaded('pdo_mysql')) {
      $suite->addTest(Test_DB_Mysql::suite());
    }

    if (extension_loaded('pgsql') && extension_loaded('pdo_pgsql')) {
      $suite->addTest(Test_DB_Pgsql::suite());
    }

    //if (extension_loaded('pdo_sqlite')) $suite->addTest(Test_DB_SQLite::suite());

    return $suite;
  }
}
