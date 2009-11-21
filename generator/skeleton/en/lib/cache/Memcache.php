<?php

class Cache_Memcache implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  private $memcache = null;
  private $prefix = "";
  
  private function __construct($server, $port)
  {
    if (defined("SERVICE_DOMAIN")) {
      $this->prefix = SERVICE_DOMAIN;
    } elseif (isset($_SERVER["SERVER_NAME"])) {
      $this->prefix = $_SERVER["SERVER_NAME"];
    } else {
      $this->prefix = "localhost";
    }
    
    $this->memcache = Sabel_Cache_Memcache::create($server, $port);
  }
  
  public static function create($server = "localhost", $port = 11211)
  {
    if (self::$instance === null) {
      self::$instance = new self($server, $port);
    }
    
    return self::$instance;
  }
  
  public function read($key)
  {
    return $this->memcache->read($this->getKey($key));
  }
  
  public function write($key, $value, $timeout = 0)
  {
    $this->memcache->write($this->getKey($key), $value, $timeout);
  }
  
  public function delete($key)
  {
    $this->memcache->delete($this->getKey($key));
  }
  
  protected function getKey($key)
  {
    return $this->prefix . "_{$key}";
  }
}
