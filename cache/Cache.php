<?php

interface Cache
{
  public function get($key);
  public function add($key, $value);
  public function delete($key);
}

class MemCacheImpl implements Cache
{
  private $memcache;
  private static $instance;

  protected function __construct($server = null)
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
      return null;
    }
  }

  public function add($key, $value, $timeout = 600, $comp = false)
  {
    try {
      $this->memcache->add($key, $value, $comp, $timeout);
    } catch (Exception $e) {
      // @todo ‚Ç‚¤‚·‚é‚Ì‚©‚µ‚ç`H
    }
  }

  public function delete($key)
  {
    $this->memcache->delete($key);
  }
}

?>
