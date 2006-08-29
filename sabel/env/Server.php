<?php

/**
 * Env_Server
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Env_Server
{
  private static $instance = null;
  
  public static function create()
  {
    if (is_not_object(self::$instance)) self::$instance = new self();
    return self::$instance;
  }
  
  public function __get($key)
  {
    $key = strtoupper($key);
    return (isset($_SERVER[$key])) ? $_SERVER[$key] : null;
  }
  
  public function isMethod($expected)
  {
    return ($expected === $this->request_method);
  }
}