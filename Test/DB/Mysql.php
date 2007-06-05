<?php

class Test_DB_Mysql extends Test_DB_Test
{
  private static $params1 = array('driver'   => 'mysql',
                                  'host'     => '192.168.0.151',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'port'     => '3308',
                                  'database' => 'edo');

  private static $params2 = array('driver'   => 'pdo-mysql',
                                  'host'     => '192.168.0.151',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'port'     => '3308',
                                  'database' => 'edo2');

  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_Mysql");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_Mysql");
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
    Sabel_DB_Config::regist('default',  self::$params1);
    Sabel_DB_Config::regist('default2', self::$params2);

    Test_DB_Test::$db = 'MYSQL';

    $tables = Test_DB_Test::$TABLES;
    $model  = Sabel_Model::load('Basic');

    $mh = new MysqlHelper();

    foreach ($mh->sqls as $query) {
      try { $model->executeQuery($query); } catch (Exception $e) {}
    }

    try {
      foreach ($tables as $table) $model->executeQuery("DELETE FROM $table");
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
      try { @$model->executeQuery($query); } catch (Exception $e) { }
    }

    $model->executeQuery('DELETE FROM customer');
    $model->executeQuery('DELETE FROM parents');
    $model->executeQuery('DELETE FROM grand_child');
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
                 city_id integer not null,
                 company_id integer not null) type=InnoDB';

    $sqls[] = 'CREATE TABLE city (
                 id integer primary key,
                 name varchar(24),
                 classification_id integer,
                 country_id integer not null) type=InnoDB';

    $sqls[] = 'CREATE TABLE company (
                 id integer primary key,
                 city_id integer not null,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE country (
                 id integer primary key,
                 planet_id integer,
                 name varchar(24)) type=InnoDB';

    $sqls[] = 'CREATE TABLE planet (
                 id integer primary key,
                 name varchar(24))';

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

    $sqls[] = "CREATE TABLE schema_test (
                 id integer primary key auto_increment,
                 name varchar(128) not null default 'test',
                 bl boolean default false comment 'boolean',
                 dt datetime,
                 ft_val float default 1,
                 db_val double not null,
                 tx text,
                 users_id integer not null,
                 city_id integer not null,
                 uni1 integer not null,
                 uni2 integer not null,
                 uni3 integer not null,
                 unique(uni1), unique(uni2, uni3),
                 foreign key(users_id) references users(id) on delete cascade on update no action,
                 foreign key(city_id) references city(id) on delete no action) type=InnoDB";

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
                 auto_update datetime,
                 auto_create datetime)";

    $sqls[] = "CREATE TABLE child (
                 id integer primary key,
                 parents_id integer not null,
                 name varchar(24),
                 height integer)";

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
