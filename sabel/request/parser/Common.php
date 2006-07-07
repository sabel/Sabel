<?php

abstract class Sabel_Request_Parser_Common
{
  protected final $attributes = null;
  protected final $parameters = null;
  
  abstract function parse($request, $pair, $pat);
  
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