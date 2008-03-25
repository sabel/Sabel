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
    $this->storage = new Sabel_Storage_Memory();
  }
  
  public function testFetch()
  {
    $this->assertNull($this->storage->fetch("hoge"));
  }
  
  public function testStore()
  {
    $this->storage->store("hoge", "abcde");
    $this->assertEquals("abcde", $this->storage->fetch("hoge"));
  }
  
  public function testClear()
  {
    $this->storage->clear("hoge");
    $this->assertNull($this->storage->fetch("hoge"));
  }
}
