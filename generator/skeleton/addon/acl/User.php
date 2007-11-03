<?php

class Acl_User
{
  const AUTHED_KEY = "authenticated";
  
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
  
  public function authenticate($role = null)
  {
    if ($role !== null) {
      $this->attributes[$role] = true;
    }
    
    $this->setAuthenticated(true);
  }
  
  public function deAuthenticate()
  {
    $this->setAuthenticated(false);
  }
  
  public function setAuthenticated($bool)
  {
    $this->attributes[self::AUTHED_KEY] = $bool;
  }
  
  public function isAuthenticated($role = null)
  {
    if ($role !== null) {
      return $this->isAuthenticatedAs($role);
    } elseif (isset($this->attributes[self::AUTHED_KEY])) {
      return $this->attributes[self::AUTHED_KEY];
    } else {
      return false;
    }
  }
  
  public function isAuthenticatedAs($role)
  {
    $attr = $this->attributes;
    $key  = self::AUTHED_KEY;
    
    if (strpos($role, "|") === false) {
      return (isset($attr[$key])  && $attr[$key]  === true &&
              isset($attr[$role]) && $attr[$role] === true);
    } else {
      foreach (explode("|", $role) as $r) {
        if ($this->isAuthenticatedAs($r)) return true;
      }
      
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
