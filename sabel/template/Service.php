<?php

interface Sabel_Template_ServiceInterface
{
  public function assign($key, $value);
  public function retrieve();
  public function selectName($name);
  public function selectPath($path);
  public function rendering();
}

class Sabel_Template_Service implements Sabel_Template_ServiceInterface
{
  private $impl;
  
  public function __construct($ins = null)
  {
    $this->impl = ($ins instanceof BaseEngineImpl) ? $ins : new Sabel_Template_Engine_PHP();
    $this->impl->configuration();
  }
  
  public static function create()
  {
    $instance = new self();
    return $instance;
  }
  
  public function changeEngine($inc)
  {
    if ($ins instanceof Sabel_Template_Engine) $this->impl = $inc;
  }
  
  public function assign($key ,$value)
  {
    $this->impl->assign($key, $value);
  }
  
  public function retrieve()
  {
    return $this->impl->retrieve();
  }
  
  public function selectName($name)
  {
    $this->impl->setTemplateName($name);
  }
  
  public function selectPath($path)
  {
    $this->impl->setTemplatePath($path);
  }
  
  public function rendering()
  {
    $this->impl->display();
  }
}