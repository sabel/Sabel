<?php

/**
 * Cache Manager
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Manager
{
  private static $support = array();
  
  public static function init()
  {
    self::$support["apc"]      = extension_loaded("apc");
    self::$support["xcache"]   = extension_loaded("xcache");
    self::$support["memcache"] = extension_loaded("memcache");
  }
  
  public static function create($type = "")
  {
    $instance = null;
    
    if (ENVIRONMENT === DEVELOPMENT || ENVIRONMENT === TEST) {
      $instance = Sabel_Cache_Null::create();
    } elseif (isset(self::$support["apc"]) && self::$support["apc"] === true) {
      $instance = Sabel_Cache_Apc::create();
    } elseif (isset(self::$support["xcache"]) && self::$support["xcache"] === true) {
      $instance = Sabel_Cache_Xcache::create();
    } elseif (isset(self::$support["memcache"]) && self::$support["memcache"] === true) {
      $instance = Sabel_Cache_Memcache::create();
    } else {
      $instance = Sabel_Cache_File::create();
    }
    
    return $instance;
  }
}