<?php

class Test_InformationSchema extends SabelTestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_InformationSchema");
  }

  public function testUse()
  {
    $schemaName = 'Default_Stest';
    $colObj = Parser::create(new $schemaName());

    $id      = $colObj['id'];
    $name    = $colObj['name'];
    $status  = $colObj['status'];
    $comment = $colObj['comment'];
    $pare_id = $colObj['pare_id'];
    $birth   = $colObj['birth'];
    $time    = $colObj['time'];
    $com     = $colObj['com'];

    $this->assertEquals($id->type, Type::INT);
    $this->assertEquals((int)$id->max, 222);
    $this->assertEquals((int)$id->min, -222);
    $this->assertFalse($id->primary);

    $this->assertEquals($name->type, Type::STRING);
    $this->assertEquals((int)$name->max, 128);
    $this->assertTrue($name->notNull);
    $this->assertFalse($name->primary);

    $this->assertEquals($status->type, Type::BOOL);
    $this->assertTrue($status->notNull);
    $this->assertFalse($status->primary);

    $this->assertEquals($comment->type, Type::STRING);
    $this->assertEquals((int)$comment->max, 64);
    $this->assertEquals($comment->default, 'varchar default');
    $this->assertFalse($comment->notNull);
    $this->assertFalse($comment->primary);

    $this->assertEquals($pare_id->type, Type::INT);
    $this->assertEquals((int)$pare_id->max, 444);
    $this->assertEquals((int)$pare_id->min, -444);
    $this->assertTrue($pare_id->increment);
    $this->assertTrue($pare_id->primary);

    $this->assertEquals($birth->type, Type::DATE);
    $this->assertEquals($birth->default, '3000-01-01');
    $this->assertTrue($birth->notNull);
    $this->assertFalse($birth->increment);
    $this->assertFalse($birth->primary);

    $this->assertEquals($time->type, Type::TIMESTAMP);
    $this->assertEquals($com->type, Type::TEXT);
  }
}

class Default_Stest
{
  public function __construct()
  {
    $dbCon = array();
    $dbCon['driver'] = 'pdo-mysql';
    $dbCon['host'] = 'localhost';
    $dbCon['user'] = 'root';
    $dbCon['password'] = '';
    $dbCon['database'] = 'edo';
    Sabel_DB_Connection::addConnection('schemaAccess', $dbCon);
  }

  public function getCreateSQL()
  {
    $sql = array();

    $sql['id'] = 'INT,222,-222,false,false,false,null';
    $sql['name'] = 'STRING,128,false,true,false,null';
    $sql['status'] = 'BOOL,false,true,false,null';
    $sql['comment'] = 'STRING,64,false,false,false,varchar default';
    $sql['pare_id'] = 'INT,444,-444,true,false,true,null';
    $sql['birth'] = 'DATE,false,true,false,3000-01-01';
    $sql['time'] = 'TIMESTAMP,false,false,false,null';
    $sql['com'] = 'TEXT,false,false,false,null';

    return $sql;
  }
}

class PgSchemaHelper
{
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'pgsql:host=localhost;dbname=edo';
    $dbCon['user'] = 'pgsql';
    $dbCon['password'] = 'pgsql';
    Sabel_DB_Connection::addConnection('schemaAccess', 'pdo', $dbCon, 'public');
  }

  public function getCreateSQL()
  {
    $sqls = array();

    $sqls[] = 'CREATE TABLE stest (
                 id   SMALLINT PRIMARY KEY,
                 name VARCHAR(128) NOT NULL,
                 status BOOLEAN NOT NULL,
                 comment VARCHAR(64) DEFAULT \'varchar default\' )';

    $sqls[] = 'CREATE TABLE stest2 (
                 id   SERIAL PRIMARY KEY,
                 birth DATE NOT NULL DEFAULT \'3000-01-01\',
                 time TIMESTAMP,
                 comment TEXT )';

    return $sqls;
  }
}

class SQSchemaHelper
{
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'sqlite:Test/data/schema.sq3';
    Sabel_DB_Connection::addConnection('schemaAccess', 'pdo', $dbCon);
  }

  public function getCreateSQL()
  {
    $sqls = array();

    $sqls[] = 'CREATE TABLE stest (
                 id   INT2 PRIMARY KEY,
                 name VARCHAR(128) NOT NULL,
                 status BOOLEAN NOT NULL,
                 comment VARCHAR(64) DEFAULT \'varchar default\' )';

    $sqls[] = 'CREATE TABLE stest2 (
                 id   INTEGER PRIMARY KEY,
                 birth DATE NOT NULL DEFAULT \'3000-01-01\',
                 time TIMESTAMP,
                 comment TEXT )';

    return $sqls;
  }
}

class Schema_Accessor extends Sabel_DB_Mapper
{
  public function __construct($param1 = null, $param2 = null)
  {
    $this->setDriver('schemaAccess');
    parent::__construct($param1, $param2);
  }
}
