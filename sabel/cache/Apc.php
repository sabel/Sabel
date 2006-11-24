<?php

/**
 * Cache implementation of APC
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Cache_Apc
{
  protected static $instance = null;
  protected $signature = '';
  
  public function __construct()
  {
    if (!extension_loaded('apc')) {
      throw new Sabel_Exception_Runtime('apc extension not loaded');
    }
    $this->signature = $_SERVER['SERVER_NAME'];
  }
  
  public static function create()
  {
    if (is_null(self::$instance)) self::$instance = new self();
    return self::$instance;
  }
  
  public function read($key)
  {
    return apc_fetch($this->signature.$key);
  }
  
  public function write($key, $value)
  {
    return apc_store($this->signature.$key, $value);
  }
  
  public function isReadable($key)
  {
    $result = $this->read($this->signature.$key);
    return ($result !== false);
  }
  
  public function delete($key)
  {
    apc_delete($this->signature.$key);
  }
}