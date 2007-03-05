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
class Sabel_Cache_Memcache implements Sabel_Cache_Cache
{
  private $memcache;
  private static $instance;

  protected function __construct($server)
  {
    $this->memcache = new Memcache();
    $this->memcache->addServer($server, 11211, true);
  }

  public static function create($server = null)
  {
    if (!isset(self::$instance)) {
      if (is_null($server)) throw new Exception("server is null.");
      self::$instance = new self($server);
    }

    return self::$instance;
  }

  public function get($key)
  {
    try {
      return $this->memcache->get($key);
    } catch (Exception $e) {
      dump("EXCEPTION" . $e->getMessage());
    }
  }

  public function add($key, $value, $timeout = 600, $comp = false)
  {
    try {
      $this->memcache->add($key, $value, $comp, $timeout);
    } catch (Exception $e) {
      dump("EXCEPTION" . $e->getMessage());
    }
  }

  public function delete($key)
  {
    $this->memcache->delete($key);
  }
}
