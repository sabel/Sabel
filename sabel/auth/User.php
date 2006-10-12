<?php

class Sabel_Auth_User
{
  protected $attributes = array();
  protected $uniqueKey  = null;

  public function __construct($uniqueKey = null)
  {
    $this->uniqueKey = $uniqueKey;

    $storage = Sabel_Storage_Session::create();
    $this->attributes = $storage->read('Community.UserAttributes' . $uniqueKey);
  }

  public function __destruct()
  {
    $storage = Storage::create('SessionStorage');
    $storage->write('Community.UserAttributes' . $this->uniqueKey, $this->attributes);
  }

  public function addAttribute($key, $value)
  {
    $this->attributes[$key] = $value;
  }

  public function removeAttribute($key)
  {
    unset($this->attributes[$key]);
  }

  public function getAttribute($key)
  {
    return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
  }
}