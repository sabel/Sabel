<?php

abstract class Sabel_Response_Abstract
{
  protected $parameters = array();
  
  public function __get($key)
  {
    return $this->parameters[$key];
  }
  
  public function __set($key, $value)
  {
    $this->parameters[$key] = $value;
  }
}
