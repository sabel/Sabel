<?php

class Test_DB_SQLite extends Test_DB_Test
{
  private static $params1 = array('driver'   => 'pdo-sqlite',
                                  'database' => 'Test/data/log1.sq3');

  private static $params2 = array('driver'   => 'pdo-sqlite',
                                  'database' => 'Test/data/log2.sq3');

  public static function main() {
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_SQLite");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_SQLite");
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

    Test_DB_Test::$db = 'SQLITE';

    $tables = Test_DB_Test::$TABLES;
    $model  = Sabel_Model::load('Basic');

    $sh = new SQLiteHelper();

    foreach ($sh->sqls as $query) {
      try { @$model->execute($query); } catch (Exception $e) {}
    }

    try {
      foreach ($tables as $table) $model->execute("DELETE FROM $table");
    } catch (Exception $e) {
    }

    $model = Sabel_Model::load('Customer');

    $sqls = array('CREATE TABLE customer( id int4 primary key, name varchar(24))',
                  'CREATE TABLE parents( id int4 primary key, name varchar(24))',
                  'CREATE TABLE grand_child( id int4 primary key, child_id integer, name varchar(24), age integer)');

    foreach ($sqls as $query) {
      try { @$model->execute($query); } catch (Exception $e) {}
    }

    $model->execute('DELETE FROM customer');
    $model->execute('DELETE FROM parents');
    $model->execute('DELETE FROM grand_child');
  }
}

/**
 * create query for sqlite unit test.
 *
 */
class SQLiteHelper
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
                 city_id int4 not null,
                 company_id integer not null)';

    $sqls[] = 'CREATE TABLE city (
                 id int4 primary key,
                 name varchar(24),
                 classification_id integer,
                 country_id int4 not null)';

    $sqls[] = 'CREATE TABLE company (
                 id integer primary key,
                 city_id integer not null,
                 name varchar(24))';

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

    $sqls[] = "CREATE TABLE schema_test (
                 id integer not null primary key,
                 name varchar(128) not null default 'test',
                 bl boolean default false,
                 dt datetime,
                 ft_val float default 1,
                 db_val double not null,
                 tx text)";

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
                 id int4 primary key,
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
