<?php

/**
 * Env_Server
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Env_Server
{
  private static $instance;
  
  public function create()
  {
    if (is_not_object(self::$instance)) self::$instance = new self();
    return self::$instance;
  }
  
  public function __get($key)
  {
    $key = strtoupper($key);
    if (isset($_SERVER[$key])) {
      return $_SERVER[$key];
    } else {
      return null;
    }
  }
  
  public function isMethod($expected)
  {
    return ($expected === $this->request_method);
  }
}
