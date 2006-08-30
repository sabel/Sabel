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
  
  protected function __construct($entry, $templateEngine)
  {
    $this->contentForLayout = '';
    
    $this->impl = ($templateEngine instanceof Sabel_Template_Engine)
                  ? $templateEngine
                  : new Sabel_Template_Engine_Class();
                  
    $this->impl->configuration();
    
    $d = Sabel_Template_Director_Factory::create($entry);
    $this->selectPath($d->decidePath());
    $this->selectName($d->decideName());
  }
  
  public static function create($entry, $templateEngine = null)
  {
    return new self($entry, $templateEngine);
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
  
  public function assignByArray($array)
  {
    $this->impl->assignByArray($array);
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