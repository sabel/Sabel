<?php

class Test_DB_Pgsql extends Test_DB_Test
{
  private static $params1 = array('driver'   => 'pdo-pgsql',
                                  'host'     => 'localhost',
                                  'user'     => 'pgsql',
                                  'password' => 'pgsql',
                                  'schema'   => 'public',
                                  'database' => 'edo');

  private static $params2 = array('driver'   => 'pgsql',
                                  'host'     => 'localhost',
                                  'user'     => 'pgsql',
                                  'password' => 'pgsql',
                                  'schema'   => 'public',
                                  'database' => 'edo2');

  public static function main()
  {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("Test_DB_Pgsql");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_DB_Pgsql");
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
    Sabel_DB_Connection::addConnection('default',  self::$params1);
    Sabel_DB_Connection::addConnection('default2', self::$params2);

    Test_DB_Test::$db = 'pgsql';
    define('DB', 'PGSQL');

    $tables = Test_DB_Test::$TABLES;
    $model  = Sabel_DB_Model::load('');

    $ph = new PgsqlHelper();
    foreach ($ph->sqls as $query) {
      try { @$model->execute($query); }
      catch (Exception $e) { }
    }

    try {
      foreach ($tables as $table) $model->execute("DELETE FROM $table");
    } catch (Exception $e) { }

    $model = Sabel_DB_Model::load('');
    $model->setConnectName('default2');

    try {
      @$model->execute('CREATE TABLE customer( id integer primary key, name varchar(24))');
    } catch (Exception $e) {
    }
  }
}

/**
 * create query for postgres unit test.
 *
 */
class PgsqlHelper
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
                 city_id integer not null)';

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
                 id serial primary key,
                 string varchar(24))';

    $sqls[] = "CREATE TABLE test_condition (
                 id serial primary key,
                 status boolean,
                 registed timestamp,
                 point integer)";

    $sqls[] = "CREATE TABLE blog (
                 id integer primary key,
                 title varchar(24),
                 article text,
                 write_date timestamp,
                 users_id integer)";

    $sqls[] = "CREATE TABLE favorite_item (
                 id integer primary key,
                 users_id integer,
                 registed timestamp,
                 name varchar(24))";

    $sqls[] = "CREATE TABLE customer_order (
                 id serial primary key,
                 customer_id integer,
                 buy_date timestamp,
                 amount integer)";

    $this->sqls = $sqls;
  }
}
