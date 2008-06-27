<?php

/**
 * Cache implementation of Memcache
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Memcache implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  private $memcache = null;
  
  private function __construct($server, $port)
  {
    if (extension_loaded("memcache")) {
      $this->memcache = new Memcache();
      $this->addServer($server, $port);
    } else {
      throw new Sabel_Exception_Runtime("memcache extension not loaded.");
    }
  }
  
  public static function create($server = "localhost", $port = 11211)
  {
    if (self::$instance === null) {
      self::$instance = new self($server, $port);
    }
    
    return self::$instance;
  }
  
  public function addServer($server, $port = 11211, $weight = 1)
  {
    $this->memcache->addServer($server, $port, true, $weight);
  }
  
  public function read($key)
  {
    $result = $this->memcache->get($key);
    return ($result === false) ? null : $result;
  }
  
  public function write($key, $value, $timeout = 0, $comp = false)
  {
    $this->memcache->set($key, $value, $comp, $timeout);
  }
  
  public function delete($key)
  {
    $this->memcache->delete($key);
  }
}
