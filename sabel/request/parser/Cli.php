<?php

uses('sabel.request.parser.Common');

class Sabel_Request_Parser_Cli extends Sabel_Request_Parser_Common
{
  private static $instance = null;
  
  private $attributes = null;
  private $parameters = null;
  
  public static function create()
  {
  
  }
  
  public function __set($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function __get($key)
  {
    if ($key == 'parameters') return $this->parameters;
    return $this->attributes[$key];
  }
  
  public function parse($request = null, $pair = null, $pat = null)
  {
    
  }
  
  public function destruct(){}
}

?>