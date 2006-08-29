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
  private $path;
  private $name;
  private $contentForLayout;
  
  public function __construct($ins = null)
  {
    $this->contentForLayout = '';
    $this->impl = ($ins instanceof Sabel_Template_Engine) ? $ins : new Sabel_Template_Engine_Class();
    $this->impl->configuration();
  }
  
  public static function create()
  {
    $instance = new self();
    return $instance;
  }
  
  public function changeEngine($ins)
  {
    if ($ins instanceof Sabel_Template_Engine) {
      $this->impl = $ins;
      $this->impl->configuration();
    } else {
    	return false;
    }
  }
  
  public function assign($key, $value)
  {
    $this->impl->assign($key, $value);
  }
  
  public function retrieve()
  {
    return $this->impl->retrieve();
  }
  
  public function selectName($name)
  {
    $this->name = $name;
    $this->impl->setTemplateName($name);
  }
  
  public function selectPath($path)
  {
    $this->path = $path;
    $this->impl->setTemplatePath($path);
  }
  
  public function rendering()
  {
    if (is_file($this->path . 'layout.tpl')) {
      $this->contentForLayout = $this->impl->load('layout.tpl');
    }
    
    echo $this->impl->retrieve();
  }
}