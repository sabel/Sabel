<?php

class Sabel_Errors
{
  protected $errors = array();
  
  public function add($name, $msg)
  {
    $this->errors[$name] = $msg;
  }
  
  public function get($name)
  {
    if (isset($this->errors[$name])) return $this->errors[$name];
  }
  
  public function toArray()
  {
    return $this->errors;
  }
}