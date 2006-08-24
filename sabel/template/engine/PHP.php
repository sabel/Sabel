<?php

class Sabel_Template_Engine_PHP extends Sabel_Template_Engine
{
  public function assign($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function put($key)
  {
    echo $this->attributes[$key];
  }
  
  public function __get($key)
  {
    return $this->attributes[$key];
  }
  
  public function configuration()
  {
  }
  
  public function retrieve()
  {
    if (count($this->attributes) != 0) extract($this->attributes, EXTR_SKIP);
    extract(Re::get(), EXTR_SKIP);
    ob_start();
    include($this->getTemplateFullPath());
    $content = ob_get_clean();
    return $content;
  }
}