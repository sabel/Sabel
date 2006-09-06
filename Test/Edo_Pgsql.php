<?php

class Test_Edo_Pgsql extends Test_Edo_Test
{
  private static $params1 = array('driver'   => 'pdo-pgsql',
                                  'host'     => 'localhost',
                                  'user'     => 'pgsql',
                                  'password' => 'pgsql',
                                  'database' => 'edo');

  private static $params2 = array('driver'   => 'pgsql',
                                  'host'     => 'localhost',
                                  'user'     => 'pgsql',
                                  'password' => 'pgsql',
                                  'database' => 'edo2');

  public static function main() {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("Test_Edo_Pgsql");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Edo_Pgsql");
  }

  public function __construct()
  {
  }

  protected function setUp()
  {
  }

  protected function tearDown()
  {
  }

  public function testInit()
  {
    Sabel_DB_Connection::addConnection('default', self::$params1);
    Sabel_DB_Connection::addConnection('default2', self::$params2);

    $tables = Test_Edo_Test::$TABLES;
    $obj = new Sabel_DB_Basic();

    try {
      foreach ($tables as $table) $obj->execute("DELETE FROM {$table}");
    } catch (Exception $e) {
    }

    $trans2 = new Trans2();
    $trans2->execute("DELETE FROM trans2");
  }
}

/**
 * create query for postgres unit test.
 *
 *
class PgsqlHelper
{
  protected $sqls = null;

  public function __construct()
  {
    $SQLs = array();

    $SQLs[] = 'CREATE TABLE test (
                 id       SERIAL PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 blood    VARCHAR(32),
                 test2_id INT2)';
    
    $SQLs[] = 'CREATE TABLE test2 (
                 id       SERIAL PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 test3_id INT2)';
                 
    $SQLs[] = 'CREATE TABLE test3 (
                id   SERIAL PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer (
                id   SERIAL PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_order (
                id          SERIAL PRIMARY KEY,
                customer_id INT2 NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE order_line (
                id                SERIAL PRIMARY KEY,
                customer_order_id INT2 NOT NULL,
                amount            INT4,
                item_id           INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_telephone (
                id          SERIAL PRIMARY KEY,
                customer_id INT2 NOT NULL,
                telephone   VARCHAR(32))';
                
    $SQLs[] = 'CREATE TABLE infinite1 (
                id           SERIAL PRIMARY KEY,
                infinite2_id INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE infinite2 (
                id           SERIAL PRIMARY KEY,
                infinite1_id int2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE seq (
                 id   SERIAL PRIMARY KEY,
                 text VARCHAR(65536) NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE tree (
                 id      SERIAL PRIMARY KEY,
                 tree_id INT2,
                 name    VARCHAR(12) )';
                
    $SQLs[] = 'CREATE TABLE student (
                 id    SERIAL PRIMARY KEY,
                 name  VARCHAR(24) NOT NULL,
                 birth DATE)';
    
    $SQLs[] = 'CREATE TABLE student_course (
                 id         SERIAL,
                 student_id INT4 NOT NULL,
                 course_id  INT4 NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id   SERIAL PRIMARY KEY,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id        SERIAL PRIMARY KEY,
                 name      VARCHAR(24) NOT NULL,
                 status_id INT2 )';

    $SQLs[] = 'CREATE TABLE status (
                 id    SERIAL PRIMARY KEY,
                 state VARCHAR(24) )';

    $SQLs[] = 'CREATE TABLE bbs (
                 id       SERIAL PRIMARY KEY,
                 users_id INT4 NOT NULL,
                 title    VARCHAR(24),
                 body     VARCHAR(24))';

    $SQLs[] = 'CREATE TABLE trans1 (
                 id    SERIAL PRIMARY KEY,
                 text  VARCHAR(24) )';

    $this->sqls = $SQLs;
  }
}
*/

if (!defined("PHPUnit2_MAIN_METHOD")) {
  define("PHPUnit2_MAIN_METHOD", "Test_Edo_Pgsql::main");
}
