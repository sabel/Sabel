<?php

require_once 'c:\php\sabel\sabel\db\Connection.php';
require_once 'c:\php\sabel\sabel\db\Transaction.php';
require_once 'c:\php\sabel\sabel\db\SimpleCache.php';

require_once 'c:\php\sabel\sabel\db\Condition.php';
require_once 'c:\php\sabel\sabel\db\Executer.php';
require_once 'c:\php\sabel\sabel\db\Mapper.php';
require_once 'c:\php\sabel\sabel\db\Relation.php';
require_once 'c:\php\sabel\sabel\db\Basic.php';
require_once 'c:\php\sabel\sabel\db\Bridge.php';
require_once 'c:\php\sabel\sabel\db\Tree.php';
require_once 'c:\php\sabel\sabel\db\driver\General.php';
require_once 'c:\php\sabel\sabel\db\driver\ResultSet.php';
require_once 'c:\php\sabel\sabel\db\driver\Statement.php';
require_once 'c:\php\sabel\sabel\db\driver\native\Query.php';
require_once 'c:\php\sabel\sabel\db\driver\native\Mssql.php';
require_once 'c:\php\sabel\sabel\db\driver\native\Paginate.php';

require_once 'c:\php\sabel\sabel\db\schema\type\Setter.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Sender.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Int.php';
require_once 'c:\php\sabel\sabel\db\schema\type\String.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Float.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Double.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Text.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Time.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Byte.php';
require_once 'c:\php\sabel\sabel\db\schema\type\Other.php';

require_once 'c:\php\sabel\sabel\db\schema\Const.php';
require_once 'c:\php\sabel\sabel\db\schema\Table.php';
require_once 'c:\php\sabel\sabel\db\schema\Column.php';
require_once 'c:\php\sabel\sabel\db\schema\Accessor.php';

require_once 'c:\php\sabel\sabel\db\schema\Common.php';
require_once 'c:\php\sabel\sabel\db\schema\General.php';
require_once 'c:\php\sabel\sabel\db\schema\Mssql.php';

require_once 'Test.php';

class MssqlExecute
{
  private static $params1 = array('driver'   => 'mssql',
                                  'host'     => 'USER-116CCEF1CD\SQLEXPRESS',
                                  'user'     => 'develop',
                                  'password' => 'test',
                                  'schema'   => 'edo',
                                  'database' => 'edo');

  private static $params2 = array('driver'   => 'mssql',
                                  'host'     => 'USER-116CCEF1CD\SQLEXPRESS',
                                  'user'     => 'develop2',
                                  'password' => 'test',
                                  'schema'   => 'edo2',
                                  'database' => 'edo2');

  public static function main()
  {
    Sabel_DB_Connection::addConnection('default',  self::$params1);
    Sabel_DB_Connection::addConnection('default2', self::$params2);

    $tables = Test_DB_Windows_Test::$TABLES;
    $obj = new Test3();

    try {
      foreach ($tables as $table) $obj->execute("DELETE FROM {$table}");
    } catch (Exception $e) {
      print_r($e->getMessage());
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

class MssqlHelper
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
                 id INTEGER NOT NULL PRIMARY KEY IDENTITY(1, 1),
                 text VARCHAR(255) NOT NULL)';
    
    $SQLs[] = 'CREATE TABLE tree (
                 id      INTEGER NOT NULL PRIMARY KEY,
                 tree_id INTEGER,
                 name    VARCHAR(12) )';

    $SQLs[] = 'CREATE TABLE student (
                 id    INTEGER NOT NULL PRIMARY KEY,
                 name  VARCHAR(24) NOT NULL,
                 birth DATETIME)';
    
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
                 id    INTEGER NOT NULL PRIMARY KEY IDENTITY(1, 1),
                 state VARCHAR(24) )';

    $SQLs[] = 'CREATE TABLE bbs (
                 id       INTEGER NOT NULL PRIMARY KEY IDENTITY(1, 1),
                 users_id INTEGER NOT NULL,
                 title    VARCHAR(24),
                 body     VARCHAR(24))';

    $SQLs[] = 'CREATE TABLE trans1 (
                 id    INTEGER NOT NULL PRIMARY KEY IDENTITY(1, 1),
                 text  VARCHAR(24))';

    $SQLs[] = "CREATE TABLE schema_test (
                 id1 bigint NOT NULL IDENTITY(1, 1),
                 id2 integer NOT NULL,
                 num integer DEFAULT 10,
                 fnum real,
                 dnum double precision,
                 str varchar(64) DEFAULT 'test',
                 text text,
                 bl bit DEFAULT 'true',
                 dt datetime not null,
                 PRIMARY KEY (id1, id2))";

    $this->sqls = $SQLs;
  }

  public function get()
  {
    return $this->sqls;
  }
}

MssqlExecute::main();
