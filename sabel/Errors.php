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
  
  public function hasError()
  {
    return (count($this->errors) > 0);
  }
  
  public function count()
  {
    return count($this->errors);
  }
  
  public function toArray()
  {
    return $this->errors;
  }
}