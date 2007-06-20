<?php

class Sabel_Plugin_Acl_User
{
  private $attributes = array();
  
  public function __set($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function __get($key)
  {
    if (array_key_exists($key, $this->attributes)) {
      return $this->attributes[$key];
    } else {
      return null;
    }
  }
  
  public function toArray()
  {
    return $this->attributes;
  }
  
  public function restore($attributes)
  {
    $this->attributes = $attributes;
  }
  
  public function setAuthenticated($bool)
  {
    $this->attributes["authenticated"] = $bool;
  }
  
  public function isAuthenticated()
  {
    if (isset($this->attributes["authenticated"])) {
      return $this->attributes["authenticated"];
    } else {
      return false;
    }
  }
  
  public function isTypeOf($compare)
  {
    if (!isset($this->attributes["type"])) return false;
    return ($this->attributes["type"] === $compare);
  }
  
  public function destroy()
  {
    $this->attributes = array();
    $this->attributes["authenticated"] = false;
  }
}
