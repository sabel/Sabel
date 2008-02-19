<?php

/**
 * testcase for sabel.storage.Memory
 *
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Storage_Memory extends SabelTestCase
{
  private $storage = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Storage_Memory");
  }
  
  public function setUp()
  {
    $this->storage = Sabel_Storage_Memory::create();
  }
  
  public function testRead()
  {
    $this->assertNull($this->storage->read("hoge"));
  }
  
  public function testWrite()
  {
    $this->storage->write("hoge", "abcde");
    $this->assertEquals("abcde", $this->storage->read("hoge"));
  }
  
  public function testDelete()
  {
    $deleted = $this->storage->delete("hoge");
    $this->assertEquals("abcde", $deleted);
    
    $this->assertNull($this->storage->delete("abcde"));
    $this->assertNull($this->storage->read("hoge"));
  }
  
  public function testDestroy()
  {
    $this->storage->write("hoge", "123");
    $this->storage->write("fuga", "987");
    $values = $this->storage->clear();
    $this->assertEquals("123", $values["hoge"]);
    $this->assertEquals("987", $values["fuga"]);
  }
}
