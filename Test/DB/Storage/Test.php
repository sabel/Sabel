<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_Test extends SabelTestCase
{
  public function testStore()
  {
    $obj = new SblStorageTestObj();
    $obj->hoge = array("int" => 10, "bool" => true);
    
    $stdClass = new stdClass();
    $stdClass->int  = 20;
    $stdClass->bool = false;
    $obj->fuga = $stdClass;
    
    $storage = new Sabel_Storage_Database();
    $storage->store("hashkey", $obj, 60);
  }
  
  public function testFetch()
  {
    $storage = new Sabel_Storage_Database();
    $obj = $storage->fetch("hashkey");
    
    $hoge = $obj->hoge;
    $fuga = $obj->fuga;
    
    $this->assertEquals(10,    $hoge["int"]);
    $this->assertEquals(true,  $hoge["bool"]);
    $this->assertEquals(20,    $fuga->int);
    $this->assertEquals(false, $fuga->bool);
  }
  
  public function testClose()
  {
    Sabel_DB_Metadata::clear();
    Sabel_DB_Connection::closeAll();
  }
}

class SblStorageTestObj extends Sabel_Object
{
  const FOO = "FOO";
  
  private $foo = null;
  protected $bar = 0;
  public $baz = false;
  
  public $hoge = null;
  public $fuga = null;
  
  private function foo() {}
  protected function bar() {}
  public function baz() {}
}
