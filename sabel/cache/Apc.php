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
  
  public function __construct()
  {
    if (!extension_loaded('apc')) {
      throw new Sabel_Exception_Runtime('apc extension not loaded');
    }
  }
  
  public static function create()
  {
    if (is_null(self::$instance)) self::$instance = new self();
    return self::$instance;
  }
  
  public function read($key)
  {
    return apc_fetch($key);
  }
  
  public function write($key, $value)
  {
    return apc_store($key, $value);
  }
  
  public function isReadable($key)
  {
    $result = $this->read($key);
    return ($result !== false);
  }
  
  public function delete($key)
  {
    apc_delete($key);
  }
}