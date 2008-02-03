<?php

/**
 * @category  Cache
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Cache_File extends Test_Cache_Test
{
  public static function suite()
  {
    define("CACHE_DIR_PATH", RUN_BASE . DIRECTORY_SEPARATOR . "cache");
    return self::createSuite("Test_Cache_File");
  }
  
  public function setUp()
  {
    $this->cache = Sabel_Cache_File::create();
    $this->cache->delete("hoge");
  }
}
