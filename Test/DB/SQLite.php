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
    Sabel_DB_Connection::addConnection('default',  self::$params1);
    Sabel_DB_Connection::addConnection('default2', self::$params2);

    $tables = Test_DB_Test::$TABLES;
    $model  = Sabel_DB_Model::load('');

    try {
      $sh = new SQLiteHelper();
      foreach ($sh->sqls as $query) $model->execute($query);
    } catch (Exception $e) {
    }

    try {
      foreach ($tables as $table) $model->execute("DELETE FROM $table");
    } catch (Exception $e) {
    }

    $model = Sabel_DB_Model::load('');
    $model->setConnectName('default2');

    try {
      $model->execute('CREATE TABLE customer( id int4 primary key, name varchar(24))');
    } catch (Exception $e) {
    }
  }
}

/**
 * create query for sqlite unit test.
 *
 *
 */
class SQLiteHelper extends BaseHelper
{
  public $sqls = null;

  public function __construct()
  {
    $sqls = array();

    $sqls[] = 'CREATE TABLE basic (
                 id int4 primary key,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE users (
                 id int4 primary key,
                 name varchar(24),
                 email varchar(128),
                 city_id int4 not null)';

    $sqls[] = 'CREATE TABLE city (
                 id int4 primary key,
                 name varchar(24),
                 classification_id integer,
                 country_id int4 not null)';

    $sqls[] = 'CREATE TABLE country (
                 id int4 primary key,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE classification (
                 id int4 primary key,
                 class_name varchar(24))';

    $sqls[] = 'CREATE TABLE test_for_like (
                 id integer primary key,
                 string varchar(24))';

    $sqls[] = "CREATE TABLE test_condition (
                 id integer primary key,
                 status boolean,
                 registed datetime,
                 point int4)";

    $sqls[] = "CREATE TABLE blog (
                 id int4 primary key,
                 title varchar(24),
                 article text,
                 write_date datetime,
                 users_id int4)";

    $sqls[] = "CREATE TABLE favorite_item (
                 id int4 primary key,
                 users_id int4,
                 registed datetime,
                 name varchar(24))";

    $sqls[] = "CREATE TABLE customer_order (
                 id integer primary key,
                 customer_id int4,
                 buy_date datetime,
                 amount integer)";

    $this->sqls = $sqls;
  }
}
