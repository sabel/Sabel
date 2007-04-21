<?php

class Sabel
{
  public static function load($clsName, $arg = null)
  {
    return new $clsName($arg);
  }

  public static function using($arg)
  {
    // ignore
  }

  public static function fileUsing($arg)
  {
    // ignore
  }
}

function convert_to_tablename($mdlName)
{
  if (preg_match('/^[a-z0-9_]+$/', $mdlName)) return $mdlName;
  return substr(strtolower(preg_replace('/([A-Z])/', '_$1', $mdlName)), 1);
}

function convert_to_modelname($tblName)
{
  return join('', array_map('ucfirst', explode('_', $tblName)));
}

function MODEL($mdlName, $arg1 = null, $arg2 = null)
{
  return Sabel_Model::load($mdlName, $arg1, $arg2);
}

function now()
{
  return date("Y-m-d H:i:s");
}

require_once 'C:\php\Sabel\sabel\Date.php';
require_once 'C:\php\Sabel\sabel\Model.php';

require_once 'C:\php\Sabel\sabel\db\Config.php';
require_once 'C:\php\Sabel\sabel\db\Connection.php';

require_once 'C:\php\Sabel\sabel\db\Model.php';
require_once 'C:\php\Sabel\sabel\db\Command.php';
require_once 'C:\php\Sabel\sabel\db\Type.php';

require_once 'C:\php\Sabel\sabel\db\command\Before.php';
require_once 'C:\php\Sabel\sabel\db\command\After.php';

require_once 'C:\php\Sabel\sabel\db\command\Executer.php';
require_once 'C:\php\Sabel\sabel\db\command\Loader.php';
require_once 'C:\php\Sabel\sabel\db\command\Base.php';
require_once 'C:\php\Sabel\sabel\db\command\Select.php';
require_once 'C:\php\Sabel\sabel\db\command\Update.php';
require_once 'C:\php\Sabel\sabel\db\command\Insert.php';
require_once 'C:\php\Sabel\sabel\db\command\Delete.php';
require_once 'C:\php\Sabel\sabel\db\command\Query.php';
require_once 'C:\php\Sabel\sabel\db\command\ArrayInsert.php';

require_once 'C:\php\Sabel\sabel\db\command\Begin.php';
require_once 'C:\php\Sabel\sabel\db\command\Commit.php';
require_once 'C:\php\Sabel\sabel\db\command\Rollback.php';

require_once 'C:\php\Sabel\sabel\db\model\Bridge.php';
require_once 'C:\php\Sabel\sabel\db\model\CascadeDelete.php';

require_once 'C:\php\Sabel\sabel\db\condition\Manager.php';
require_once 'C:\php\Sabel\sabel\db\condition\Object.php';
require_once 'C:\php\Sabel\sabel\db\condition\And.php';
require_once 'C:\php\Sabel\sabel\db\condition\Or.php';

require_once 'C:\php\Sabel\sabel\db\condition\builder\Interface.php';
require_once 'C:\php\Sabel\sabel\db\condition\builder\Loader.php';
require_once 'C:\php\Sabel\sabel\db\condition\builder\Base.php';
require_once 'C:\php\Sabel\sabel\db\condition\builder\Common.php';
require_once 'C:\php\Sabel\sabel\db\condition\builder\Pdo.php';

require_once 'C:\php\Sabel\sabel\db\relation\Joiner.php';
require_once 'C:\php\Sabel\sabel\db\relation\Join.php';
require_once 'C:\php\Sabel\sabel\db\relation\Key.php';
require_once 'C:\php\Sabel\sabel\db\relation\join\Object.php';
require_once 'C:\php\Sabel\sabel\db\relation\join\Counterfeit.php';
require_once 'C:\php\Sabel\sabel\db\relation\join\Alias.php';
require_once 'C:\php\Sabel\sabel\db\relation\join\Result.php';

require_once 'C:\php\Sabel\sabel\db\transaction\Base.php';
require_once 'C:\php\Sabel\sabel\db\transaction\Ibase.php';
require_once 'C:\php\Sabel\sabel\db\transaction\Common.php';

require_once 'C:\php\Sabel\sabel\db\sql\Interface.php';
require_once 'C:\php\Sabel\sabel\db\sql\Loader.php';
require_once 'C:\php\Sabel\sabel\db\sql\Common.php';
require_once 'C:\php\Sabel\sabel\db\sql\Pdo.php';

require_once 'C:\php\Sabel\sabel\db\sql\constraint\Interface.php';
require_once 'C:\php\Sabel\sabel\db\sql\constraint\Loader.php';
require_once 'C:\php\Sabel\sabel\db\sql\constraint\Common.php';
require_once 'C:\php\Sabel\sabel\db\sql\constraint\Ibase.php';
require_once 'C:\php\Sabel\sabel\db\sql\constraint\Mssql.php';

require_once 'C:\php\Sabel\sabel\db\schema\Interface.php';
require_once 'C:\php\Sabel\sabel\db\schema\Accessor.php';
require_once 'C:\php\Sabel\sabel\db\schema\Table.php';
require_once 'C:\php\Sabel\sabel\db\schema\Loader.php';
require_once 'C:\php\Sabel\sabel\db\schema\Base.php';
require_once 'C:\php\Sabel\sabel\db\schema\Common.php';
require_once 'C:\php\Sabel\sabel\db\schema\Mysql.php';
require_once 'C:\php\Sabel\sabel\db\schema\Pgsql.php';
require_once 'C:\php\Sabel\sabel\db\schema\Sqlite.php';
require_once 'C:\php\Sabel\sabel\db\schema\Mssql.php';
require_once 'C:\php\Sabel\sabel\db\schema\Ibase.php';

require_once 'C:\php\Sabel\sabel\db\type\Setter.php';
require_once 'C:\php\Sabel\sabel\db\type\Interface.php';
require_once 'C:\php\Sabel\sabel\db\type\Integer.php';
require_once 'C:\php\Sabel\sabel\db\type\String.php';
require_once 'C:\php\Sabel\sabel\db\type\Float.php';
require_once 'C:\php\Sabel\sabel\db\type\Double.php';
require_once 'C:\php\Sabel\sabel\db\type\Text.php';
require_once 'C:\php\Sabel\sabel\db\type\Date.php';
require_once 'C:\php\Sabel\sabel\db\type\Time.php';
require_once 'C:\php\Sabel\sabel\db\type\Datetime.php';
require_once 'C:\php\Sabel\sabel\db\type\Byte.php';
require_once 'C:\php\Sabel\sabel\db\type\Other.php';

require_once 'C:\php\Sabel\sabel\db\driver\Base.php';
require_once 'C:\php\Sabel\sabel\db\driver\Mysql.php';
require_once 'C:\php\Sabel\sabel\db\driver\Pgsql.php';
require_once 'C:\php\Sabel\sabel\db\driver\Mssql.php';
require_once 'C:\php\Sabel\sabel\db\driver\Ibase.php';
require_once 'C:\php\Sabel\sabel\db\driver\Pdo.php';
require_once 'C:\php\Sabel\sabel\db\driver\Sequence.php';

require_once 'Test.php';

class FirebirdExecute
{
  private static $params1 = array('driver'   => 'ibase',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => 'D:\Apache Group\Apache2\htdocs\EDO.FDB');

  private static $params2 = array('driver'   => 'ibase',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => 'D:\Apache Group\Apache2\htdocs\EDO2.FDB');

  public static function main()
  {
    Sabel_DB_Config::regist('default',  self::$params1);
    Sabel_DB_Config::regist('default2', self::$params2);

    $tables = Test_DB_Windows_Test::$TABLES;
    $model  = Sabel_Model::load('Basic');

    try {
      @$model->executeQuery('CREATE GENERATOR TEST_FOR_LIKE_ID_GEN');
      @$model->executeQuery('CREATE GENERATOR TEST_CONDITION_ID_GEN');
      @$model->executeQuery('CREATE GENERATOR CUSTOMER_ORDER_ID_GEN');
      @$model->executeQuery('CREATE GENERATOR SCHEMA_TEST_ID_GEN');
    } catch (Exception $e) {
    }

    $mh = new FirebirdHelper();

    foreach ($mh->sqls as $query) {
      try { @$model->executeQuery($query); } catch (Exception $e) {}
    }

    try {
      foreach ($tables as $table) @$model->executeQuery("DELETE FROM $table");
      @$model->executeQuery("DROP TABLE parents");
      @$model->executeQuery("DROP TABLE grand_child");
      @$model->executeQuery("DROP TABLE customer");
    } catch (Exception $e) {
    }

    $model = Sabel_Model::load('Customer');

    $sqls = array('CREATE TABLE customer( id integer primary key, name varchar(24)) type=InnoDB',
                  'CREATE TABLE parents( id integer primary key, name varchar(24))',
                  'CREATE TABLE grand_child( id integer primary key, child_id integer, name varchar(24), age integer)');

    foreach ($sqls as $query) {
      try { @$model->executeQuery($query); } catch (Exception $e) {}
    }

    $model->executeQuery('DELETE FROM customer');
    $model->executeQuery('DELETE FROM parents');
    $model->executeQuery('DELETE FROM grand_child');

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

class FirebirdHelper
{
  public $sqls = array();

  public function __construct()
  {
    $sqls = array();

    $sqls[] = 'CREATE TABLE basic (
                 id integer primary key,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE users (
                 id integer primary key,
                 name varchar(24),
                 email varchar(128),
                 city_id integer not null,
                 company_id integer not null)';

    $sqls[] = 'CREATE TABLE city (
                 id integer primary key,
                 name varchar(24),
                 classification_id integer,
                 country_id integer not null)';

    $sqls[] = 'CREATE TABLE company (
                 id integer primary key,
                 city_id integer not null,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE country (
                 id integer primary key,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE classification (
                 id integer primary key,
                 class_name varchar(24))';

    $sqls[] = 'CREATE TABLE test_for_like (
                 id integer primary key,
                 string varchar(24))';

    $sqls[] = "CREATE TABLE test_condition (
                 id integer primary key,
                 status smallint,
                 registed timestamp,
                 point integer)";

    $sqls[] = "CREATE TABLE blog (
                 id integer primary key,
                 title varchar(24),
                 article blob sub_type text,
                 write_date timestamp,
                 users_id integer)";

    $sqls[] = "CREATE TABLE favorite_item (
                 id integer primary key,
                 users_id integer,
                 registed timestamp,
                 name varchar(24))";

    $sqls[] = "CREATE TABLE customer_order (
                 id integer primary key,
                 customer_id integer,
                 buy_date timestamp,
                 amount integer)";

    $sqls[] = "CREATE TABLE schema_test (
                 id integer primary key,
                 name varchar(128) default 'test' not null,
                 bl char(1) default '0',
                 dt timestamp,
                 ft_val float default 1,
                 db_val double precision not null,
                 tx blob sub_type text)";

    $sqls[] = "CREATE TABLE student (
                 id integer primary key,
                 name varchar(24))";

    $sqls[] = "CREATE TABLE course (
                 id integer primary key,
                 course_name varchar(24))";

    $sqls[] = "CREATE TABLE student_course (
                 student_id integer not null,
                 course_id  integer not null,
                 primary key (student_id, course_id))";

    $sqls[] = "CREATE TABLE timer (
                 id integer primary key,
                 auto_update timestamp,
                 auto_create timestamp)";

    $sqls[] = "CREATE TABLE child (
                 id integer primary key,
                 parents_id integer not null,
                 name varchar(24),
                 height integer)";

    $sqls[] = "CREATE TABLE mail (
                 id integer primary key,
                 sender_id integer not null,
                 recipient_id integer not null,
                 subject varchar(255))";

    $this->sqls = $sqls;
  }
}

FirebirdExecute::main();
