<?php

class Test_InformationSchema extends SabelTestCase
{
  public static function suite()
  {
    //$hel = new MySchemaHelper();
    //$hel = new PgSchemaHelper();
    $hel = new SQSchemaHelper();

    $obj = new Schema_Accessor();
    $sqls = $hel->getCreateSQL();
    foreach ($sqls as $sql) $obj->execute($sql);

    return new PHPUnit2_Framework_TestSuite("Test_InformationSchema");
  }

  public function testUse()
  {
    $dropTables = array('stest', 'stest2');

    //$sa = new Sabel_DB_Schema_Accessor('schemaAccess', 'edo');
    $sa = new Sabel_DB_Schema_Accessor('schemaAccess', 'public');

    $stest  = $sa->getTable('stest');

    $id      = $stest->getColumnByName('id');
    $name    = $stest->getColumnByName('name');
    $status  = $stest->getColumnByName('status');
    $comment = $stest->getColumnByName('comment');

    $this->assertEquals($id->type, Sabel_DB_Schema_Type::INT);
    $this->assertEquals((int)$id->max, 32767);
    $this->assertTrue($id->primary);

    $this->assertEquals($name->type, Sabel_DB_Schema_Type::STRING);
    $this->assertEquals((int)$name->max, 128);
    $this->assertTrue($name->notNull);
    $this->assertFalse($name->primary);

    $this->assertEquals($status->type, Sabel_DB_Schema_Type::BOOL);
    $this->assertTrue($status->notNull);
    $this->assertFalse($status->primary);

    $this->assertEquals($comment->type, Sabel_DB_Schema_Type::STRING);
    $this->assertEquals((int)$comment->max, 64);
    $this->assertEquals($comment->default, 'varchar default');
    $this->assertFalse($comment->notNull);
    $this->assertFalse($comment->primary);

    $stest2 = $sa->getTable('stest2');

    $id      = $stest2->getColumnByName('id');
    $birth   = $stest2->getColumnByName('birth');
    $time    = $stest2->getColumnByName('time');
    $comment = $stest2->getColumnByName('comment');

    $this->assertEquals($id->type, Sabel_DB_Schema_Type::INT);
    $this->assertEquals((int)$id->max, 2147483647);
    $this->assertTrue($id->increment);
    $this->assertTrue($id->primary);

    $this->assertEquals($birth->type, Sabel_DB_Schema_Type::DATE);
    $this->assertEquals($birth->default, '3000-01-01');
    $this->assertTrue($birth->notNull);
    $this->assertFalse($birth->increment);
    $this->assertFalse($birth->primary);

    $this->assertEquals($time->type, Sabel_DB_Schema_Type::TIMESTAMP);
    $this->assertEquals($comment->type, Sabel_DB_Schema_Type::TEXT);


    $obj = new Schema_Accessor();
    foreach ($dropTables as $table) $obj->execute("DROP TABLE {$table}");
  }
}

class MySchemaHelper
{
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'mysql:host=localhost;dbname=edo';
    $dbCon['user'] = 'root';
    $dbCon['pass'] = '';
    Sabel_DB_Connection::addConnection('schemaAccess', 'pdo', $dbCon);
  }

  public function getCreateSQL()
  {
    $sqls = array();

    $sqls[] = 'CREATE TABLE stest (
                 id      INT2 PRIMARY KEY,
                 name    VARCHAR(128) NOT NULL,
                 status  BOOLEAN NOT NULL COMMENT \'boolean\',
                 comment VARCHAR(64) DEFAULT \'varchar default\' )';

    $sqls[] = 'CREATE TABLE stest2 (
                 id      INT4 PRIMARY KEY AUTO_INCREMENT,
                 birth   DATE NOT NULL DEFAULT \'3000-01-01\',
                 time    DATETIME,
                 comment TEXT )';

    return $sqls;
  }
}

class PgSchemaHelper
{
  public function __construct()
  {
    $dbCon = array();
    $dbCon['dsn']  = 'pgsql:host=localhost;dbname=edo';
    $dbCon['user'] = 'pgsql';
    $dbCon['pass'] = 'pgsql';
    Sabel_DB_Connection::addConnection('schemaAccess', 'pdo', $dbCon);
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
