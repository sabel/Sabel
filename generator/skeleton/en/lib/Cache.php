<?php

class Cache
{
  protected static $storage = null;
  
  public static function setStorage(Sabel_Cache_Interface $storage)
  {
    self::$storage = $storage;
  }
  
  public static function getStorage()
  {
    return self::$storage;
  }
  
  public static function get($key)
  {
    return self::$storage->read($key);
  }
  
  public static function set($key, $value, $timeout = 0)
  {
    self::$storage->write($key, $value, $timeout);
  }
  
  public static function delete($key)
  {
    self::$storage->delete($key);
  }
}

if ((ENVIRONMENT & PRODUCTION) > 0) {
  // APC
  // Cache::setStorage(Cache_Apc::create());
  
  // Memcache
  // Cache::setStorage(Cache_Memcache::create("localhost", 11211));
  
  // File
  Cache::setStorage(Cache_File::create(CACHE_DIR_PATH));
} else {
  Cache::setStorage(Sabel_Cache_Null::create());
}
