<?php

class Test_DB_SQLite extends Test_DB_Test
{
  private static $params1 = array('driver'   => 'pdo-sqlite',
                                  'database' => 'Test/data/log1.sq3');

  private static $params2 = array('driver'   => 'pdo-sqlite',
                                  'database' => 'Test/data/log2.sq3');

  public static function main() {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("Test_DB_SQLite");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_DB_SQLite");
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
    Test_DB_Test::$connectName = 'sq';
    Sabel_DB_Connection::addConnection('sq', self::$params1);
    Sabel_DB_Connection::addConnection('sq2', self::$params2);

    $tables = Test_DB_Test::$TABLES;
    $obj = new Test3();
    
    $helper = new SQLiteHelper();
    $helper->create();

    try {
      foreach ($tables as $table) $obj->execute("DELETE FROM {$table}");
    } catch (Exception $e) {
    }

    $trans2 = new Trans2();
    $trans2->execute("DELETE FROM trans2");
  }
}

/**
 * create query for sqlite unit test.
 *
 *
 */
class SQLiteHelper extends BaseHelper
{
  protected $sqls = null;

  public function __construct()
  {
    $SQLs = array();

    $SQLs[] = 'CREATE TABLE test (
                 id       INTEGER PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 blood    VARCHAR(32),
                 test2_id INT2)';
    
    $SQLs[] = 'CREATE TABLE test2 (
                 id       INTEGER PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 test3_id INT2)';
                 
    $SQLs[] = 'CREATE TABLE test3 (
                 id   INTEGER PRIMARY KEY,
                 name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer (
                 id   INTEGER PRIMARY KEY,
                 name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_order (
                 id          INTEGER PRIMARY KEY,
                 customer_id INT2 NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE order_line (
                 id                INTEGER PRIMARY KEY,
                 customer_order_id INT2 NOT NULL,
                 amount            INT4,
                 item_id           INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_telephone (
                 id          INTEGER PRIMARY KEY,
                 customer_id INT2 NOT NULL,
                 telephone   VARCHAR(32))';
                
    $SQLs[] = 'CREATE TABLE infinite1 (
                 id           INTEGER PRIMARY KEY,
                 infinite2_id INT2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE infinite2 (
                 id           INTEGER PRIMARY KEY,
                 infinite1_id int2 NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE seq (
                 id   INTEGER PRIMARY KEY,
                 text VARCHAR(65536) NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE tree (
                 id      INTEGER PRIMARY KEY,
                 tree_id INT2,
                 name    VARCHAR(12) )';
                
    $SQLs[] = 'CREATE TABLE student (
                 id    INTEGER PRIMARY KEY,
                 name  VARCHAR(24) NOT NULL,
                 birth DATE)';
    
    $SQLs[] = 'CREATE TABLE student_course (
                 student_id INT4 NOT NULL,
                 course_id  INT4 NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id   INTEGER PRIMARY KEY,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id        INTEGER PRIMARY KEY,
                 name      VARCHAR(24) NOT NULL,
                 status_id INT2 )';

    $SQLs[] = 'CREATE TABLE status (
                 id    INTEGER PRIMARY KEY,
                 state VARCHAR(24) )';

    $SQLs[] = 'CREATE TABLE bbs (
                 id       INTEGER PRIMARY KEY,
                 users_id INT4 NOT NULL,
                 title    VARCHAR(24),
                 body     VARCHAR(24))';

    $SQLs[] = 'CREATE TABLE trans1 (
                 id    INTEGER PRIMARY KEY,
                 text  VARCHAR(24) )';

    $SQLs[] = "CREATE TABLE schema_test (
                 id1 int8 not null,
                 id2 int4 not null,
                 num integer default 10,
                 fnum float4,
                 dnum float8,
                 str varchar(64) default 'test',
                 text text,
                 bl boolean default true,
                 date date,
                 dt timestamp not null,
                 primary key (id1, id2));";
                 
    $this->sqls = $SQLs;
  }

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
