<?php

/**
 * Cache implementation of APC
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Apc implements Sabel_Cache_Interface
{
  private $signature = "";
  private static $instance = null;
  
  public function __construct()
  {
    if (!extension_loaded("apc")) {
      throw new Sabel_Exception_Runtime("apc extension not loaded");
    }
    
    if (isset($_SERVER["SERVER_NAME"])) {
      $this->signature = $_SERVER["SERVER_NAME"];
    } else {
      $this->signature = PHP_VERSION;
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
    return apc_fetch($this->signature . $key);
  }
  
  public function write($key, $value, $timeout = 600, $comp = false)
  {
    return apc_store($this->signature . $key, $value);
  }
  
  public function delete($key)
  {
    return apc_delete($this->signature . $key);
  }
  
  public function isReadable($key)
  {
    $result = $this->read($this->signature . $key);
    return ($result !== false);
  }
}
