<?php

class Test_DB_Ibase extends Test_DB_Test
{
  private static $params1 = array('driver'   => 'ibase',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => '/home/firebird/edo.fdb');

  private static $params2 = array('driver'   => 'ibase',
                                  'host'     => 'localhost',
                                  'user'     => 'develop',
                                  'password' => 'develop',
                                  'encoding' => 'utf8',
                                  'database' => '/home/firebird/edo2.fdb');
  public static function main()
  {
    require_once "PHPUnit/TextUI/TestRunner.php";

    $suite  = new PHPUnit_Framework_TestSuite("Test_DB_Ibase");
    $result = PHPUnit_TextUI_TestRunner::run($suite);
  }

  public static function suite()
  {
    return self::createSuite("Test_DB_Ibase");
  }

  public function __construct()
  {
  }

  public function setUp()
  {
  }

  public function tearDown()
  {
  }

  public function testInit()
  {
    Sabel_DB_Config::regist('default',  self::$params1);
    Sabel_DB_Config::regist('default2', self::$params2);

    Test_DB_Test::$db = "IBASE";

    $tables = Test_DB_Test::$TABLES;
    $model  = Sabel_Model::load('Basic');
    foreach ($tables as $table) @$model->executeQuery("DELETE FROM $table");

    /*
    //do run. but once !!

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
      @$model->executeQuery("DROP TABLE customer");
    } catch (Exception $e) {
    }

    $model = Sabel_Model::load('Customer');
    $sql = 'CREATE TABLE customer( id integer not null primary key, name varchar(24))';
    try { @$model->executeQuery($sql); } catch (Exception $e) {}

    */
    $model = Sabel_Model::load('Customer');
    $model->executeQuery('DELETE FROM customer');
  }
}

class FirebirdHelper
{
  public $sqls = array();

  public function __construct()
  {
    $sqls = array();

    $sqls[] = 'CREATE TABLE basic (
                 id integer not null primary key,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE users (
                 id integer not null primary key,
                 name varchar(24),
                 email varchar(128),
                 city_id integer not null,
                 company_id integer not null)';

    $sqls[] = 'CREATE TABLE city (
                 id integer not null primary key,
                 name varchar(24),
                 classification_id integer,
                 country_id integer not null)';

    $sqls[] = 'CREATE TABLE company (
                 id integer not null primary key,
                 city_id integer not null,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE country (
                 id integer not null primary key,
                 planet_id integer,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE planet (
                 id integer not null primary key,
                 name varchar(24))';

    $sqls[] = 'CREATE TABLE classification (
                 id integer not null primary key,
                 class_name varchar(24))';

    $sqls[] = 'CREATE TABLE test_for_like (
                 id integer not null primary key,
                 string varchar(24))';

    $sqls[] = "CREATE TABLE test_condition (
                 id integer not null primary key,
                 status smallint,
                 registed timestamp,
                 point integer)";

    $sqls[] = "CREATE TABLE blog (
                 id integer not null primary key,
                 title varchar(24),
                 article blob sub_type text,
                 write_date timestamp,
                 users_id integer)";

    $sqls[] = "CREATE TABLE favorite_item (
                 id integer not null primary key,
                 users_id integer,
                 registed timestamp,
                 name varchar(24))";

    $sqls[] = "CREATE TABLE customer_order (
                 id integer not null primary key,
                 customer_id integer,
                 buy_date timestamp,
                 amount integer)";

    $sqls[] = "CREATE TABLE schema_test (
                 id integer not null primary key,
                 name varchar(128) default 'test' not null,
                 bl char(1) default '0',
                 dt timestamp,
                 ft_val float default 1,
                 db_val double precision not null,
                 tx blob sub_type text,
                 users_id integer not null,
                 city_id integer not null,
                 uni1 integer not null,
                 uni2 integer not null,
                 uni3 integer not null,
                 unique(uni1), unique(uni2, uni3),
                 foreign key(users_id) references users(id) on delete cascade on update no action,
                 foreign key(city_id) references city(id) on delete no action)";

    $sqls[] = "CREATE TABLE student (
                 id integer not null primary key,
                 name varchar(24))";

    $sqls[] = "CREATE TABLE course (
                 id integer not null primary key,
                 course_name varchar(24))";

    $sqls[] = "CREATE TABLE student_course (
                 student_id integer not null,
                 course_id  integer not null,
                 primary key (student_id, course_id))";

    $sqls[] = "CREATE TABLE timer (
                 id integer not null primary key,
                 auto_update timestamp,
                 auto_create timestamp)";

    $sqls[] = "CREATE TABLE child (
                 id integer not null primary key,
                 parents_id integer not null,
                 name varchar(24),
                 height integer)";

    $sqls[] = "CREATE TABLE mail (
                 id integer not null primary key,
                 sender_id integer not null,
                 recipient_id integer not null,
                 subject varchar(255))";

    $this->sqls = $sqls;
  }
}
