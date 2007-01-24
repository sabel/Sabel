<?php

class Sabel_Storage_InMemory
{
  private static $instance = null;
  private $attributes = array();
  
  public static function create()
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
  
  public function clear()
  {
    $deleted = array();
    foreach ($this->attributes as $key => $sesval) {
      $deleted[] = $sesval;
      unset($this->attributes[$key]);
    }
    return $deleted;
  }
  
  public function destroy()
  {
    $this->attributes = array();
    return $this->attributes;
  }
  
  public function has($key)
  {
    return isset($this->attributes[$key]);
  }
  
  public function read($key)
  {
    $ret = null;
    if (isset($this->attributes[$key])) {
      $ret = $this->attributes[$key]['value'];
    }
    return $ret;
  }
  
  public function write($key, $value, $timeout = 60)
  {
    $this->attributes[$key] = array('value'   => $value, 
                            'timeout' => $timeout,
                            'count'   => 0);
  }
  
  public function delete($key)
  {
    $ret = null;
    if (isset($this->attributes[$key])) {
      $ret =& $this->attributes[$key]['value'];
      unset($this->attributes[$key]);
    }
    return $ret;
  }
  
  public function timeout()
  {
    foreach ($this->attributes as $key => $value) {
      if ($value['count'] > $value['timeout']) {
        unset($this->attributes[$key]);
      }
    }
  }
  
  public function countUp()
  {
    foreach ($this->attributes as $key => $value) {
      $this->attributes[$key]['count'] += 1;
    }
  }
}