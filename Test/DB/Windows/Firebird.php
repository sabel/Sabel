<?php

require_once 'C:\php\Sabel\sabel\db\Functions.php';

require_once 'C:\php\Sabel\sabel\db\Connection.php';
require_once 'C:\php\Sabel\sabel\db\Transaction.php';
require_once 'C:\php\Sabel\sabel\db\SimpleCache.php';
require_once 'C:\php\Sabel\sabel\db\Condition.php';
require_once 'C:\php\Sabel\sabel\db\Property.php';
require_once 'C:\php\Sabel\sabel\db\Executer.php';
require_once 'C:\php\Sabel\sabel\db\Relation.php';
require_once 'C:\php\Sabel\sabel\db\Tree.php';
require_once 'C:\php\Sabel\sabel\db\Bridge.php';
require_once 'C:\php\Sabel\sabel\db\Model.php';
require_once 'C:\php\Sabel\sabel\db\Fusion.php';

require_once 'C:\php\Sabel\sabel\db\driver\Driver.php';
require_once 'C:\php\Sabel\sabel\db\driver\Firebird.php';
require_once 'C:\php\Sabel\sabel\db\driver\ResultSet.php';
require_once 'C:\php\Sabel\sabel\db\driver\ResultObject.php';

require_once 'C:\php\Sabel\sabel\db\statement\Statement.php';
require_once 'C:\php\Sabel\sabel\db\statement\NonBind.php';
require_once 'C:\php\Sabel\sabel\db\statement\Limitation.php';

require_once 'C:\php\Sabel\sabel\db\schema\Const.php';
require_once 'C:\php\Sabel\sabel\db\schema\Accessor.php';
require_once 'C:\php\Sabel\sabel\db\schema\Column.php';
require_once 'C:\php\Sabel\sabel\db\schema\Table.php';
require_once 'C:\php\Sabel\sabel\db\schema\Common.php';
require_once 'C:\php\Sabel\sabel\db\schema\Firebird.php';

require_once 'C:\php\Sabel\sabel\db\schema\type\Setter.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Sender.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Integer.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\String.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Float.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Double.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Text.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Time.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Byte.php';
require_once 'C:\php\Sabel\sabel\db\schema\type\Other.php';

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
    $model  = Sabel_DB_Model::load('Basic');

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

    $model = Sabel_DB_Model::load('Customer');

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

    $this->sqls = $sqls;
  }
}

FirebirdExecute::main();
