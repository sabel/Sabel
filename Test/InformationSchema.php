<?php

class Test_InformationSchema extends SabelTestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_InformationSchema");
  }

  public function testUse()
  {
    $sb = new Sabel_DB_Basic('stest');
    $table = $sb->getTableSchema();

    $id      = $table->id;
    $name    = $table->name;
    $status  = $table->status;
    $comment = $table->comment;
    $pare_id = $table->pare_id;
    $birth   = $table->birth;
    $time    = $table->time;
    $com     = $table->com;

    $this->assertEquals($id->type, Sabel_DB_Schema_Type::INT);
    $this->assertEquals((int)$id->max, 222);
    $this->assertEquals((int)$id->min, -222);
    $this->assertTrue($id->notNull);
    $this->assertTrue($id->increment);
    $this->assertFalse($id->primary);

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

    $this->assertEquals($pare_id->type, Sabel_DB_Schema_Type::INT);
    $this->assertEquals((int)$pare_id->max, 444);
    $this->assertEquals((int)$pare_id->min, -444);
    $this->assertTrue($pare_id->increment);
    $this->assertTrue($pare_id->primary);

    $this->assertEquals($birth->type, Sabel_DB_Schema_Type::DATE);
    $this->assertEquals($birth->default, '3000-01-01');
    $this->assertTrue($birth->notNull);
    $this->assertFalse($birth->increment);
    $this->assertFalse($birth->primary);

    $this->assertEquals($time->type, Sabel_DB_Schema_Type::TIMESTAMP);
    $this->assertEquals($com->type, Sabel_DB_Schema_Type::TEXT);
  }
}

class Default_Stest
{
  public function get()
  {
    $sql = array();

    $sql['id']      = array('INT',222,-222,true,true,false,22);
    $sql['name']    = array('STRING',128,false,true,false,null);
    $sql['status']  = array('BOOL',false,true,false,null);
    $sql['comment'] = array('STRING',64,false,false,false,'varchar default');
    $sql['pare_id'] = array('INT',444,-444,true,false,true,null);
    $sql['birth']   = array('DATE',false,true,false,'3000-01-01');
    $sql['time']    = array('TIMESTAMP',false,false,false,null);
    $sql['com']     = array('TEXT',false,false,false,null);

    return $sql;
  }
}
