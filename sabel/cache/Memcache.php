<?php

uses('sabel.cache.Cache');

class Sabel_Cache_Memcache implements Sabel_Cache_Cache
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
      return @$this->memcache->get($key);
    } catch (Exception $e) {
      return null;
    }
  }

  public function add($key, $value, $timeout = 600, $comp = false)
  {
    try {
      $this->memcache->add($key, $value, $comp, $timeout);
    } catch (Exception $e) {
      // @todo Ç«Ç§Ç∑ÇÈÇÃÇ©ÇµÇÁÅ`ÅH
    }
  }

  public function delete($key)
  {
    $this->memcache->delete($key);
  }
}

?>