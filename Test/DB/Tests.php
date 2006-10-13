<?php

require_once('Test/DB/Test.php');
require_once('Test/DB/Mysql.php');
require_once('Test/DB/Pgsql.php');
require_once('Test/DB/SQLite.php');

require_once('Test/DB/InformationSchema.php');

class Test_DB_Tests
{
  public static function main()
  {
    PHPUnit2_TextUI_TestRunner::run(self::suite());
  }

  public static function suite()
  {
    $suite = new PHPUnit2_Framework_TestSuite();
    
    $suite->addTest(Test_DB_Mysql::suite());
    $suite->addTest(Test_DB_Pgsql::suite());
    $suite->addTest(Test_DB_SQLite::suite());
    $suite->addTest(Test_DB_InformationSchema::suite());
    
    return $suite;
  }
}

abstract class BaseHelper
{
  public function create()
  {
    foreach ($this->sqls as $sql) {
      $obj = new Test3();
      try {
        $obj->execute($sql);
      } catch(Exception $e) {
        // ignore any errors.
      }
    }
  }
}
