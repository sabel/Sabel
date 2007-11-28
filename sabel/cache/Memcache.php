<?php

/**
 * Cache implementation of Memcache
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_Memcache implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  private $memcache = null;
  
  private function __construct($server)
  {
    if (extension_loaded("memcache")) {
      $this->memcache = new Memcache();
      $this->memcache->connect($server, 11211, true);
    }
  }
  
  public static function create($server = "localhost")
  {
    if ($server === null) {
      throw new Sabel_Exception_Runtime("server is null.");
    }
    
    if (self::$instance === null) {
      self::$instance = new self($server);
    }
    
    return self::$instance;
  }
  
  public function read($key)
  {
    try {
      return $this->memcache->get($key);
    } catch (Exception $e) {
      // @todo
    }
  }
  
  public function write($key, $value, $timeout = 600, $comp = false)
  {
    try {
      $this->memcache->add($key, $value, $comp, $timeout);
    } catch (Exception $e) {
      // @todo
    }
  }
  
  public function delete($key)
  {
    $this->memcache->delete($key);
  }
}
