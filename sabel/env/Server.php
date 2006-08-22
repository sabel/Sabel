<?php

/**
 * Env_Server
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel_Env_Server
{
  public function __get($key)
  {
    $key = strtoupper($key);
    if (isset($_SERVER[$key])) {
      return $_SERVER[$key];
    } else {
      return null;
    }
  }
}
