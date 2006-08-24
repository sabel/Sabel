<?php

class Re
{
  protected static $responses = array();
  
  public static function set($name, $value)
  {
    self::$responses[$name] = $value;
  }
  public static function get()
  {
    return self::$responses;
  }
}