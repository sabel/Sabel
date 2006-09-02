<?php

abstract class Sabel_Template_Engine
{
  protected
    $tplpath = null,
    $tplname = null,
    $attributes = array();
    
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
  
  public function load($name)
  {
    $t = clone $this;
    $t->setTemplateName($name . '.tpl');
    return $t->retrieve();
  }
}
