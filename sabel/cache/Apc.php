<?php

/**
 * Cache implementation of APC
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Apc implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  private function __construct()
  {
    if (!extension_loaded("apc")) {
      $message = __METHOD__ . "() apc extension not loaded.";
      throw new Sabel_Exception_Runtime($message);
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
    $result = apc_fetch($key);
    return ($result === false) ? null : $result;
  }
  
  public function write($key, $value, $timeout = 0)
  {
    apc_store($key, $value, $timeout);
  }
  
  public function delete($key)
  {
    apc_delete($key);
  }
}
