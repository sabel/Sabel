<?php

if (!defined("PHPUnit2_MAIN_METHOD")) {
    define("PHPUnit2_MAIN_METHOD", "Test_Edo::main");
}

require_once "sabel/Functions.php";
require_once "sabel/core/Context.php";

require_once "sabel/db/driver/Interface.php";
require_once "sabel/db/query/Interface.php";
require_once "sabel/db/query/Factory.php";

require_once "sabel/db/Mapper.php";
require_once "sabel/db/InformationSchema.php";
require_once "sabel/db/Connection.php";

require_once "sabel/db/driver/Pdo.php";
require_once "sabel/db/driver/Pgsql.php";

/**
 * test for Sabel_DB
 *
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Test_DB extends SabelTestCase
{
  public static function main() {
    $suite  = new PHPUnit2_Framework_TestSuite("Test_Edo");
    $result = PHPUnit2_TextUI_TestRunner::run($suite);
  }

  public function __construct()
  {
    $h = new MysqlHelper();
    $h->initialize();
  }

  public function testFirst()
  {
    $writer = new Writer(2);
    foreach($writer->book as $book) $this->assertEquals('よつばと！', $book->name);
  }
}

class MysqlHelper
{
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=edo';
    $dbCon['user'] = 'root';
    $dbCon['pass'] = '';

    Sabel_DB_Connection::addConnection('user', 'pdo', $dbCon);
  }

  public function initialize()
  {
    $sqls[] = 'DROP TABLE writer;';
    $sqls[] = 'DROP TABLE book;';

    $sqls[] = 'CREATE TABLE writer(id int8 auto_increment primary key, name varchar(32) not null);';
    $sqls[] = 'CREATE TABLE book(id int8 auto_increment primary key, name varchar(64) not null, volume int8 default 1, writer_id int8 not null);';

    $sqls[] = 'INSERT INTO writer(name) VALUES("ツガノガク");';
    $sqls[] = 'INSERT INTO writer(name) VALUES("あずま きよひこ");';

    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("時をかける少女", 1, 1)';
    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("時をかける少女", 2, 1)';
    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("涼宮ハルヒの憂鬱", 1, 1)';
    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("涼宮ハルヒの憂鬱", 2, 1)';
    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("よつばと！", 1, 2)';
    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("よつばと！", 2, 2)';
    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("よつばと！", 3, 2)';
    $sqls[] = 'INSERT INTO book(name, volume, writer_id) VALUES("よつばと！", 4, 2)';

    $obj = new Sabel_DB_Basic();
    foreach ($sqls as $sql) {
      try {
        @$obj->execute($sql);
      } catch (Exception $e) {
      }
    }
  }
}

abstract class Mapper_Default extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('user');
    parent::__construct($param1, $param2);
  }
}

class Writer extends Mapper_Default
{
  protected $myChildren = 'book';
  protected $defChildConstraints = array('limit' => 5);
}

class Book extends Mapper_Default
{
  protected $withParent = true;
}

if (PHPUnit2_MAIN_METHOD == "Test_Edo::main") {
    Test_Edo::main();
}
