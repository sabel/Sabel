<?php

abstract class Flow_Page extends Sabel_Controller_Page
{
  public function __get($name)
  {
    if (isset($this->attributes["flow"])) {
      $value = $this->attributes["flow"]->read($name);
      return ($value === null) ? parent::__get($name) : $value;
    } else {
      return parent::__get($name);
    }
  }
  
  public function __set($name, $value)
  {
    if (isset($this->attributes["flow"])) {
      $this->attributes["flow"]->write($name, $value);
    }
    
    parent::__set($name, $value);
  }
  
  public function setAttribute($name, $value)
  {
    $this->__set($name, $value);
  }
  
  public function setAttributes($attributes)
  {
    if (isset($this->attributes["flow"])) {
      $flow = $this->attributes["flow"];
      foreach ($attributes as $name => $value) {
        $flow->write($name, $value);
      }
    }
    
    parent::setAttributes($attributes);
  }
  
  public function getAttribute($name)
  {
    return $this->__get($name);
  }
  
  public function hasAttribute($name)
  {
    if (isset($this->attributes["flow"])) {
      $bool = $this->attributes["flow"]->has($name);
      return ($bool) ? true : parent::hasAttribute($name);
    } else {
      return parent::hasAttribute($name);
    }
  }
}
