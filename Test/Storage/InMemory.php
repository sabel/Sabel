<?php

/**
 * @category  Storage
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Test_Storage_InMemory extends SabelTestCase
{
  private $storage = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Storage_InMemory");
  }
  
  public function setUp()
  {
    $this->storage = Sabel_Storage_InMemory::create();
    $this->storage->start();
  }
  
  public function testIsStarted()
  {
    $this->assertTrue($this->storage->isStarted());
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
    $this->assertNull($this->storage->read("hoge"));
  }
  
  public function testDestroy()
  {
    $this->storage->write("hoge", "123");
    $this->storage->write("fuga", "987");
    $values = $this->storage->destroy();
    $this->assertEquals("123", $values["hoge"]["value"]);
    $this->assertEquals("987", $values["fuga"]["value"]);
  }
}
