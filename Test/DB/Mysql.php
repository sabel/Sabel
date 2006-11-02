<?php

class Test_DB_Mysql extends Test_DB_Test
{
  private static $params1 = array('driver'   => 'mysql',
                                  'host'     => 'localhost',
                                  'user'     => 'root',
                                  'password' => '',
                                  'schema'   => 'edo',
                                  'database' => 'edo');

  private static $params2 = array('driver'   => 'pdo-mysql',
                                  'host'     => 'localhost',
                                  'user'     => 'root',
                                  'password' => '',
                                  'schema'   => 'edo2',
                                  'database' => 'edo2');

  public static function main() {
    require_once "PHPUnit2/TextUI/TestRunner.php";

    $suite  = new PHPUnit2_Framework_TestSuite("Test_DB_Mysql");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_DB_Mysql");
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

    $tables = Test_DB_Test::$TABLES;
    $model  = Sabel_DB_Model::load('');

    try {
      $mh = new MysqlHelper();
      foreach ($mh->sqls as $query) $query; $model->execute($query);
    } catch (Exception $e) {
    }

    try {
      foreach ($tables as $table) $model->execute("DELETE FROM $table");
    } catch (Exception $e) {
    }

    $model = Sabel_DB_Model::load('Customer');

    try {
      $model->execute('CREATE TABLE customer( id integer primary key, name varchar(24)) type=InnoDB');
    } catch (Exception $e) {
    }
  }
}

/**
 * create query for mysql unit test.
 *
 */
class MysqlHelper
{
  public $sqls = null;

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
                 city_id integer not null) type=InnoDB';

    $sqls[] = 'CREATE TABLE city (
                 id integer primary key,
                 name varchar(24),
                 classification_id integer,
                 country_id integer not null) type=InnoDB';

    $sqls[] = 'CREATE TABLE country (
                 id integer primary key,
                 name varchar(24)) type=InnoDB';

    $sqls[] = 'CREATE TABLE classification (
                 id integer primary key,
                 class_name varchar(24))';

    $sqls[] = 'CREATE TABLE test_for_like (
                 id integer primary key auto_increment,
                 string varchar(24))';

    $sqls[] = "CREATE TABLE test_condition (
                 id integer primary key auto_increment,
                 status boolean comment 'boolean',
                 registed datetime,
                 point integer)";

    $sqls[] = "CREATE TABLE blog (
                 id integer primary key,
                 title varchar(24),
                 article text,
                 write_date datetime,
                 users_id integer)";

    $sqls[] = "CREATE TABLE favorite_item (
                 id integer primary key,
                 users_id integer,
                 registed datetime,
                 name varchar(24))";

    $sqls[] = "CREATE TABLE customer_order (
                 id integer primary key auto_increment,
                 customer_id integer,
                 buy_date datetime,
                 amount integer) type=InnoDB";

    $this->sqls = $sqls;
  }
}
