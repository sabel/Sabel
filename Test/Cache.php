<?php

/**
 * TestCase for Cacahe
 *
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Test_Cache extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Cache");
  }
  
  public function setUp()
  {
    $lines = array("test1\n", "test2\n", "test3\n");
    $cache = new Sabel_Cache_File('Test/data/');
    $cache->write('cache.txt', $lines);
  }
  
  public function tearDown()
  {
    unlink('Test/data/cache.txt');
  }
  
  public function testFileCache()
  {
    $cache = new Sabel_Cache_File();
    $lines = $cache->read('Test/data/cache.txt');
    $this->assertEquals('test1', trim($lines[0]));
    $this->assertEquals('test2', trim($lines[1]));
    $this->assertEquals('test3', trim($lines[2]));
  }
  
  public function testFileCacheWithPrefix()
  {
    $cache = new Sabel_Cache_File('Test/data/');
    $lines = $cache->read('cache.txt');
    $this->assertEquals('test1', trim($lines[0]));
    $this->assertEquals('test2', trim($lines[1]));
    $this->assertEquals('test3', trim($lines[2]));
  }
  
  public function testAppendFileCacahe()
  {
    $cache = new Sabel_Cache_File('Test/data/');
    $lines = array("test4\n", "test5\n", "test6\n");
    $cache->append('cache.txt', $lines);
    $cache->append('cache.txt', "test7\n");
    $lines = $cache->read('cache.txt');
    $this->assertEquals('test4', trim($lines[3]));
    $this->assertEquals('test5', trim($lines[4]));
    $this->assertEquals('test6', trim($lines[5]));
    $this->assertEquals('test7', trim($lines[6]));
  }
}