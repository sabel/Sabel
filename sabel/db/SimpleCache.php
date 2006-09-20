<?php

class Sabel_DB_SimpleCache
{
  private static $cache = array();

  public static function add($key, $val)
  {
    self::$cache[$key] = $val;
  }

  public static function get($key)
  {
    return (isset(self::$cache[$key])) ? self::$cache[$key] : null;
  }
}
