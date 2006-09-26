<?php

class Test_DB_InformationSchema extends SabelTestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_DB_InformationSchema");
  }

  public function testUse()
  {
    $param   = array();
    $param['driver']   = 'mysql';
    $param['host']     = 'localhost';
    $param['database'] = 'edo';
    $param['schema']   = 'edo';
    $param['user']     = 'root';
    $param['password'] = '';
    Sabel_DB_Connection::addConnection('default', $param);

    $sb = new Sabel_DB_Basic('stest');
    $sb->setDriver('default');
    $table = $sb->getTableSchema();

    $id      = $table->id;
    $name    = $table->name;
    $status  = $table->status;
    $comment = $table->comment;
    $pare_id = $table->pare_id;
    $birth   = $table->birth;
    $time    = $table->time;
    $com     = $table->com;

    $this->assertEquals($id->type, Sabel_DB_Const::INT);
    $this->assertEquals((int)$id->max, 222);
    $this->assertEquals((int)$id->min, -222);
    $this->assertTrue($id->notNull);
    $this->assertTrue($id->increment);
    $this->assertFalse($id->primary);

    $this->assertEquals($name->type, Sabel_DB_Const::STRING);
    $this->assertEquals((int)$name->max, 128);
    $this->assertTrue($name->notNull);
    $this->assertFalse($name->primary);

    $this->assertEquals($status->type, Sabel_DB_Const::BOOL);
    $this->assertTrue($status->notNull);
    $this->assertFalse($status->primary);

    $this->assertEquals($comment->type, Sabel_DB_Const::STRING);
    $this->assertEquals((int)$comment->max, 64);
    $this->assertEquals($comment->default, 'varchar default');
    $this->assertFalse($comment->notNull);
    $this->assertFalse($comment->primary);

    $this->assertEquals($pare_id->type, Sabel_DB_Const::INT);
    $this->assertEquals((int)$pare_id->max, 444);
    $this->assertEquals((int)$pare_id->min, -444);
    $this->assertTrue($pare_id->increment);
    $this->assertTrue($pare_id->primary);

    $this->assertEquals($birth->type, Sabel_DB_Const::DATE);
    $this->assertEquals($birth->default, '3000-01-01');
    $this->assertTrue($birth->notNull);
    $this->assertFalse($birth->increment);
    $this->assertFalse($birth->primary);

    $this->assertEquals($time->type, Sabel_DB_Const::TIMESTAMP);
    $this->assertEquals($com->type, Sabel_DB_Const::TEXT);
  }
}

class Default_Stest
{
  public function get()
  {
    $sql = array();

    $sql['id']      = array('type' => 'INT', 'max' => 222, 'min' => -222, 'increment' => true,
                            'notNull' => true, 'primary' => false, 'default' => 22);
    $sql['name']    = array('type' => 'STRING', 'max' => 128, 'increment' => false,
                            'notNull' => true, 'primary' => false, 'default' => null);
    $sql['status']  = array('type' => 'BOOL', 'increment' => false, 'notNull' => true,
                            'primary' => false, 'default' => null);
    $sql['comment'] = array('type' => 'STRING', 'max' => 64, 'increment' => false,
                            'notNull' => false, 'primary' => false, 'default' => 'varchar default');
    $sql['pare_id'] = array('type' => 'INT', 'max' => 444, 'min' => -444, 'increment' => true,
                            'notNull' => false, 'primary' => true, 'default' => null);
    $sql['birth']   = array('type' => 'DATE', 'increment' => false, 'notNull' => true,
                            'primary' => false, 'default' => '3000-01-01');
    $sql['time']    = array('type' => 'TIMESTAMP', 'increment' => false, 'notNull' => false,
                            'primary' => false, 'default' => null);
    $sql['com']     = array('type' => 'TEXT', 'increment' => false, 'notNull' => false,
                            'primary' => false, 'default' => null);

    return $sql;
  }
}
