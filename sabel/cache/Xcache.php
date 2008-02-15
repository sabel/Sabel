<?php

 /**
  * Cache implementation of Xcache
  *
  * @category   Cache
  * @package    org.sabel.cache
  * @author     Mori Reo <mori.reo@sabel.jp>
  * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
  * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
  */
class Sabel_Cache_Xcache implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  private function __construct()
  {
    if (!extension_loaded("xcache")) {
      throw new Sabel_Exception_Runtime("xcache extension not loaded.");
    }
  }
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function read($key)
  {
    return xcache_get($key);
  }
  
  public function write($key, $value, $timeout = 0)
  {
    xcache_set($key, $value);
  }
  
  public function delete($key)
  {
    xcache_delete($key);
  }
  
  public function isReadable($key)
  {
    return xcache_isset($key);
  }
}
