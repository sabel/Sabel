<?php

class Cache_File
{
  protected static $cache = null;
  
  public static function create($dir = CACHE_DIR_PATH)
  {
    if (self::$cache === null) {
      self::$cache = Sabel_Cache_File::create($dir);
    }
    
    return self::$cache;
  }
}
