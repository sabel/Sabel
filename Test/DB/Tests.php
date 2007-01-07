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
    $suite = new PHPUnit_Framework_TestSuite();

    if (extension_loaded('mysql') && extension_loaded('pdo_mysql')) {
      $suite->addTest(Test_DB_Mysql::suite());
    }

    if (extension_loaded('pgsql') && extension_loaded('pdo_pgsql')) {
      $suite->addTest(Test_DB_Pgsql::suite());
    }

    if (extension_loaded('pdo_sqlite')) $suite->addTest(Test_DB_SQLite::suite());

    return $suite;
  }
}

if (!function_exists('get_db_tables')) {

function get_db_tables($tblName)
{
  $tables = array();
  $tables['basic']          = 'default';
  $tables['users']          = 'default';
  $tables['city']           = 'default';
  $tables['country']        = 'default';
  $tables['company']        = 'default';
  $tables['test_for_like']  = 'default';
  $tables['test_condition'] = 'default';
  $tables['blog']           = 'default';
  $tables['customer_order'] = 'default';
  $tables['classification'] = 'default';
  $tables['favorite_item']  = 'default';
  $tables['student']        = 'default';
  $tables['course']         = 'default';
  $tables['student_course'] = 'default';
  $tables['schema_test']    = 'default';
  $tables['timer']          = 'default';
  $tables['child']          = 'default';

  $tables['customer']       = 'default2';
  $tables['parents']        = 'default2';
  $tables['grand_child']    = 'default2';
  $tables['order_line']     = 'default2';

  if (!isset($tables[$tblName])) {
    throw new Exception("Error: '{$tblName}' does not exist.");
  }

  return $tables[$tblName];
}

}
