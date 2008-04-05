<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_DB_Storage_Test extends SabelTestCase
{
  protected $chars = "a'あいうb\"c\r能\nd\\e表\000申\032わをんg";
  
  public function testStore()
  {
    $s = new SerializeTest();
    $s->foo($this->chars);
    $data = array("obj" => $s, "int" => 10, "bool" => true);
    
    $storage = new Sabel_Storage_Database();
    $storage->store("hashkey", $data, 60);
  }
  
  public function testFetch()
  {
    $storage = new Sabel_Storage_Database();
    $data = $storage->fetch("hashkey");
    
    $this->assertEquals(10, $data["int"]);
    $this->assertEquals(true, $data["bool"]);
    $this->assertEquals($this->chars, $data["obj"]->foo);
  }
  
  public function testClose()
  {
    Sabel_DB_Metadata::clear();
    Sabel_DB_Connection::closeAll();
  }
}
