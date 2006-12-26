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

require_once 'C:\php\Sabel\sabel\db\Functions.php';

require_once 'C:\php\Sabel\sabel\Model.php';
require_once 'C:\php\Sabel\sabel\ValueObject.php';
require_once 'C:\php\Sabel\sabel\db\Connection.php';
require_once 'C:\php\Sabel\sabel\db\Transaction.php';
require_once 'C:\php\Sabel\sabel\db\SimpleCache.php';
require_once 'C:\php\Sabel\sabel\db\Condition.php';
require_once 'C:\php\Sabel\sabel\db\model\Property.php';
require_once 'C:\php\Sabel\sabel\db\Executer.php';
require_once 'C:\php\Sabel\sabel\db\Model.php';
require_once 'C:\php\Sabel\sabel\db\model\Relation.php';
require_once 'C:\php\Sabel\sabel\db\model\Tree.php';
require_once 'C:\php\Sabel\sabel\db\model\Bridge.php';
require_once 'C:\php\Sabel\sabel\db\model\Fusion.php';

require_once 'C:\php\Sabel\sabel\db\base\Driver.php';
require_once 'C:\php\Sabel\sabel\db\base\Statement.php';
require_once 'C:\php\Sabel\sabel\db\base\Schema.php';
require_once 'C:\php\Sabel\sabel\db\general\Statement.php';
require_once 'C:\php\Sabel\sabel\db\result\Row.php';
require_once 'C:\php\Sabel\sabel\db\result\Object.php';

require_once 'C:\php\Sabel\sabel\db\firebird\Driver.php';
require_once 'C:\php\Sabel\sabel\db\firebird\Transaction.php';
require_once 'C:\php\Sabel\sabel\db\firebird\Schema.php';
require_once 'C:\php\Sabel\sabel\db\firebird\Statement.php';

require_once 'C:\php\Sabel\sabel\db\type\Const.php';
require_once 'C:\php\Sabel\sabel\db\schema\Interface.php';
require_once 'C:\php\Sabel\sabel\db\schema\Accessor.php';
require_once 'C:\php\Sabel\sabel\db\schema\Table.php';

require_once 'C:\php\Sabel\sabel\db\type\Setter.php';
require_once 'C:\php\Sabel\sabel\db\type\Interface.php';
require_once 'C:\php\Sabel\sabel\db\type\Integer.php';
require_once 'C:\php\Sabel\sabel\db\type\String.php';
require_once 'C:\php\Sabel\sabel\db\type\Float.php';
require_once 'C:\php\Sabel\sabel\db\type\Double.php';
require_once 'C:\php\Sabel\sabel\db\type\Text.php';
require_once 'C:\php\Sabel\sabel\db\type\Time.php';
require_once 'C:\php\Sabel\sabel\db\type\Datetime.php';
require_once 'C:\php\Sabel\sabel\db\type\Byte.php';
require_once 'C:\php\Sabel\sabel\db\type\Other.php';

require_once 'Test.php';

class FirebirdExecute
{
  private static $params1 = array('driver'   => 'firebird',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => 'C:\Program Files\Firebird\db\EDO.FDB');

  private static $params2 = array('driver'   => 'firebird',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => 'C:\Program Files\Firebird\db\EDO2.FDB');

  public static function main()
  {
    Sabel_DB_Connection::addConnection('default',  self::$params1);
    Sabel_DB_Connection::addConnection('default2', self::$params2);

    $tables = Test_DB_Windows_Test::$TABLES;
    $model  = Sabel_Model::load('Basic');

    try {
      @$model->execute('CREATE GENERATOR TEST_FOR_LIKE_ID_GEN');
      @$model->execute('CREATE GENERATOR TEST_CONDITION_ID_GEN');
      @$model->execute('CREATE GENERATOR CUSTOMER_ORDER_ID_GEN');
      @$model->execute('CREATE GENERATOR SCHEMA_TEST_ID_GEN');
    } catch (Exception $e) {
    }

    $mh = new FirebirdHelper();

    foreach ($mh->sqls as $query) {
      try { @$model->execute($query); } catch (Exception $e) {}
    }

    try {
      foreach ($tables as $table) @$model->execute("DELETE FROM $table");
    } catch (Exception $e) {
    }

    $model = Sabel_Model::load('Customer');

    try {
      @$model->execute('CREATE TABLE customer( id integer primary key, name varchar(24))');
    } catch (Exception $e) {
    }

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

    $sqls[] = 'CREATE TABLE company (
                 id integer primary key,
                 city_id integer not null,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE city (
                 id integer primary key,
                 name varchar(24),
                 classification_id integer,
                 country_id integer not null)';

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
                 name varchar(128) not null,
                 bl smallint,
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

    $this->sqls = $sqls;
  }
}

FirebirdExecute::main();
