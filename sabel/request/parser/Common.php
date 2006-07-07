<?php

abstract class Sabel_Request_Parser_Common
{
  protected $attributes = null;
  protected $parameters = null;
  
  protected static $instance = null;
  
  abstract function parse($request = null, $pair = null, $pat = null);
  
  public function __set($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function __get($key)
  {
    if ($key == 'parameters') return $this->parameters;
    return $this->attributes[$key];
  }

  public function destruct()
  {
    self::$instance = null;
  }
}

?>