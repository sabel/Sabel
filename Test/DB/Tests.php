<?php

define("MODELS_DIR_PATH", "/");
require_once("Test/DB/Test.php");
require_once("Test/DB/Mysql.php");
require_once("Test/DB/Pgsql.php");
require_once("Test/DB/SQLite.php");
require_once("Test/DB/Ibase.php");
require_once("Test/DB/Oci.php");

class Condition extends Sabel_DB_Condition{}

define("EQUAL",         Condition::EQUAL);
define("ISNULL",        Condition::ISNULL);
define("ISNOTNULL",     Condition::ISNOTNULL);
define("IN",            Condition::IN);
define("BETWEEN",       Condition::BETWEEN);
define("LIKE",          Condition::LIKE);
define("GREATER_EQUAL", Condition::GREATER_EQUAL);
define("GREATER_THAN",  Condition::GREATER_THAN);
define("LESS_EQUAL",    Condition::LESS_EQUAL);
define("LESS_THAN",     Condition::LESS_THAN);
define("DIRECT",        Condition::DIRECT);

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

    if (extension_loaded("mysql") && extension_loaded("pdo_mysql")) {
      $suite->addTest(Test_DB_Mysql::suite());
    }

    if (extension_loaded("pgsql") && extension_loaded("pdo_pgsql")) {
      $suite->addTest(Test_DB_Pgsql::suite());
    }

    if (extension_loaded("pdo_sqlite")) {
      $suite->addTest(Test_DB_SQLite::suite());
    }

    if (extension_loaded("oci8")) {
      $suite->addTest(Test_DB_Oci::suite());
    }

    if (extension_loaded("interbase")) {
      $suite->addTest(Test_DB_Ibase::suite());
    }

    return $suite;
  }
}
