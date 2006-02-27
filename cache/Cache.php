<?php

abstract class Cache
{
  abstract public function get($key);
  abstract public function add($key, $value);
  abstract public function delete($key);
}

class MemCacheImpl extends Cache
{
  private $memcache;
  private static $instance;

  public function __construct()
  {
    $this->memcache = new Memcache();
    $this->memcache->addServer('192.168.0.197', 11211, true);
    $this->memcache->addServer('192.168.0.191', 11211, true);

    $this->memcache->addServer('127.0.0.1', 11211, true, 1);
  }

  public static function create()
  {
    if (!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function __destruct()
  {
    //$this->memcache->close();
  }

  public function get($key)
  {
    return $this->memcache->get($key);
  }

  public function add($key, $value)
  {
    $this->memcache->add($key, $value);
  }

  public function delete($key)
  {
    $this->memcache->delete($key);
  }
}

?>