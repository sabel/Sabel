<?php

 /**
  * Cache implementation of Xcache
  *
  * @category   Cache
  * @package    org.sabel.cache
  * @author     Mori Reo <mori.reo@gmail.com>
  * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
  * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
  */
class Sabel_Cache_Xcache
{
  private $signature = '';
  private static $instance = null;
  
  public function __construct()
  {
    if (!extension_loaded('xcache')) {
      throw new Sabel_Exception_Runtime('xcache extension not loaded');
    }
    if (isset($_SERVER['SERVER_NAME'])) {
      $this->signature = $_SERVER['SERVER_NAME'];
    } else {
      $this->signature = PHP_VERSION;
    }
  }
  
  public static function create()
  {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }
  
  public function read($key)
  {
    return xcache_get($this->signature.$key);
  }
  
  public function write($key, $value)
  {
    return xcache_set($this->signature.$key, $value);
  }
  
  public function isReadable($key)
  {
    return xcache_isset($this->signature.$key);
  }
  
  public function delete($key)
  {
    return xcache_delete($this->signature.$key);
  }
}