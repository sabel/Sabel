<?php

require_once '../db/Const.php';
require_once '../db/Connection.php';
require_once '../db/Transaction.php';
require_once '../db/SimpleCache.php';

require_once '../db/Mapper.php';
require_once '../db/BaseClasses.php';
require_once '../db/driver/General.php';
require_once '../db/driver/Query.php';
require_once '../db/driver/native/Query.php';
require_once '../db/driver/native/Firebird.php';

require_once 'Test.php';

class Execute
{
  private static $params1 = array('driver'   => 'firebird',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => 'C:\Program Files\Apache\Apache2\htdocs\firebird\EDO.FDB');

  private static $params2 = array('driver'   => 'firebird',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => 'C:/Program Files/Apache/Apache2/htdocs/firebird/EDO2.FDB');
  public static function main()
  {
    Sabel_DB_Connection::addConnection('default',  self::$params1);
    Sabel_DB_Connection::addConnection('default2', self::$params2);

    $tables = Test_DB_Windows_Test::$TABLES;
    $obj = new Test3();

    /*
    $obj->begin();
    try {
      foreach ($tables as $table) $obj->execute("CREATE GENERATOR {$table}_ID_GEN");
    } catch (Exception $e) {
      print_r($e->getMessage());
    }
    $obj->commit();
    */

    try {
      foreach ($tables as $table) $obj->execute("DELETE FROM {$table}");
    } catch (Exception $e) {
    }

    $trans2 = new Trans2();
    $trans2->execute("DELETE FROM trans2");
    
    $testMethods = array();

    $class = new ReflectionClass('Test_DB_Windows_Test'); 
    foreach ($class->getMethods() as $methodObj) {
      $methodName = $methodObj->name;
      if (($pre = substr($methodName, 0, 4)) === 'test') {
        $testMethods[] = $methodName;
      }
    }

    $t = new Test_DB_Windows_Test();
    foreach ($testMethods as $method) {
      $t->$method();
    }
  }
}

/*
class FirebirdHelper
{
  protected $sqls = null;

  public function __construct()
  {
    $SQLs = array();

    $SQLs[] = 'CREATE TABLE test (
                 id       INTEGER NOT NULL PRIMARY KEY,
                 name     VARCHAR(32) NOT NULL,
                 blood    VARCHAR(32),
                 test2_id INTEGER)';

    $SQLs[] = 'CREATE TABLE test2 (
                 id INTEGER NOT NULL PRIMARY KEY,
                 name VARCHAR(32) NOT NULL,
                 test3_id INTEGER)';
                 
    $SQLs[] = 'CREATE TABLE test3 (
                id INTEGER NOT NULL PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer (
                id INTEGER NOT NULL PRIMARY KEY,
                name VARCHAR(32) NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_order (
                id INTEGER NOT NULL PRIMARY KEY,
                customer_id INTEGER NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE order_line (
                id INTEGER NOT NULL PRIMARY KEY,
                customer_order_id INTEGER NOT NULL,
                amount INTEGER,
                item_id INTEGER NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE customer_telephone (
                id INTEGER NOT NULL PRIMARY KEY,
                customer_id INTEGER NOT NULL,
                telephone VARCHAR(32))';
                
    $SQLs[] = 'CREATE TABLE infinite1 (
                id INTEGER NOT NULL PRIMARY KEY,
                infinite2_id INTEGER NOT NULL)';
                
    $SQLs[] = 'CREATE TABLE infinite2 (
                id INTEGER NOT NULL PRIMARY KEY,
                infinite1_id INTEGER NOT NULL)';
 
    $SQLs[] = 'CREATE TABLE seq (
                 id INTEGER NOT NULL PRIMARY KEY,
                 text VARCHAR(255) NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE tree (
                 id      INTEGER NOT NULL PRIMARY KEY,
                 tree_id INTEGER,
                 name    VARCHAR(12) )';

    $SQLs[] = 'CREATE TABLE student (
                 id    INTEGER NOT NULL PRIMARY KEY,
                 name  VARCHAR(24) NOT NULL,
                 birth DATE)';
    
    $SQLs[] = 'CREATE TABLE student_course (
                 student_id INTEGER NOT NULL,
                 course_id  INTEGER NOT NULL,
                 CONSTRAINT student_course_pkey PRIMARY KEY (student_id, course_id) )';

    $SQLs[] = 'CREATE TABLE course (
                 id   INTEGER NOT NULL PRIMARY KEY,
                 name VARCHAR(24) )';
                
    $SQLs[] = 'CREATE TABLE users (
                 id        INTEGER NOT NULL PRIMARY KEY,
                 name      VARCHAR(24) NOT NULL,
                 status_id INTEGER )';

    $SQLs[] = 'CREATE TABLE status (
                 id    INTEGER NOT NULL PRIMARY KEY,
                 state VARCHAR(24) )';

    $SQLs[] = 'CREATE TABLE bbs (
                 id       INTEGER NOT NULL PRIMARY KEY,
                 users_id INTEGER NOT NULL,
                 title    VARCHAR(24),
                 body     VARCHAR(24))';

    $SQLs[] = 'CREATE TABLE trans1 (
                 id    INTEGER NOT NULL PRIMARY KEY,
                 text  VARCHAR(24))';

    $this->sqls = $SQLs;
  }

  public function get()
  {
    return $this->sqls;
  }
}
*/

Execute::main();
