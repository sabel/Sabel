<?php

/**
 * Cache implementation of APC
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Cache_Apc
{
  public function __construct()
  {
    if (!extension_loaded('apc')) {
      throw new Sabel_Exception_Runtime('apc extension not loaded');
    }
  }
  
  public function read($key)
  {
    return apc_fetch($key);
  }
  
  public function write($key, $value)
  {
    return apc_store($key, $value);
  }
  
  public function delete($key)
  {
    apc_delete($key);
  }
}