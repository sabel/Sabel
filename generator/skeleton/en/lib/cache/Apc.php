<?php

class Cache_Apc implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  private $apc = null;
  private $prefix = "";
  
  private function __construct()
  {
    if (defined("SERVICE_DOMAIN")) {
      $this->prefix = SERVICE_DOMAIN;
    } elseif (isset($_SERVER["SERVER_NAME"])) {
      $this->prefix = $_SERVER["SERVER_NAME"];
    } else {
      $this->prefix = "localhost";
    }
    
    $this->apc = Sabel_Cache_Apc::create();
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
    return $this->apc->read($this->getKey($key));
  }
  
  public function write($key, $value, $timeout = 0)
  {
    $this->apc->write($this->getKey($key), $value, $timeout);
  }
  
  public function delete($key)
  {
    $this->apc->delete($this->getKey($key));
  }
  
  protected function getKey($key)
  {
    return $this->prefix . "_{$key}";
  }
}
