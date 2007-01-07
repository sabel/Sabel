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
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_Pgsql");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return new PHPUnit_Framework_TestSuite("Test_DB_Pgsql");
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

    Test_DB_Test::$db = 'PGSQL';

    $tables = Test_DB_Test::$TABLES;
    $model  = Sabel_Model::load('Basic');

    $ph = new PgsqlHelper();

    foreach ($ph->sqls as $query) {
      try {
        @$model->execute($query);
      } catch (Exception $e) {}
    }

    try {
      foreach ($tables as $table) $model->execute("DELETE FROM $table");
    } catch (Exception $e) { }

    $model = Sabel_Model::load('Customer');

    $sqls = array('CREATE TABLE customer( id integer primary key, name varchar(24))',
                  'CREATE TABLE parents( id integer primary key, name varchar(24))',
                  'CREATE TABLE grand_child( id integer primary key, child_id integer, name varchar(24), age integer)');

    foreach ($sqls as $query) {
      try { @$model->execute($query); } catch (Exception $e) {}
    }

    $model->execute('DELETE FROM customer');
    $model->execute('DELETE FROM parents');
    $model->execute('DELETE FROM grand_child');
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

    $sqls[] = "CREATE TABLE schema_test (
                 id serial primary key,
                 name varchar(128) not null default 'test',
                 bl boolean default false,
                 dt timestamp,
                 ft_val float4 default 1,
                 db_val double precision not null,
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
