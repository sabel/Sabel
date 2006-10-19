<?php

abstract class Sabel_Template_Engine
{
  protected
    $tplpath = null,
    $tplname = null,
    $attributes = array(),
    $trim = true;
    
  public function setTemplateName($name)
  {
    $this->tplname = $name;
  }
  
  public function setTemplatePath($path)
  {
    $this->tplpath = $path;
  }
  
  protected function getTemplateFullPath()
  {
    return $this->tplpath . $this->tplname;
  }
  
  protected function getHelperPath()
  {
    
  }
  
  public function assgin($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function partial($tplName)
  {
    return $this->load($tplName);
  }
  
  public function load($name)
  {
    $t = clone $this;
    $t->setTemplateName($name . '.tpl');
    return $t->retrieve();
  }
}
