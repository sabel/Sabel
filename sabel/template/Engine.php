<?php

interface Sabel_Template_EngineInterface
{
  public function assign($key, $value);
  public function retrieve();
  public function setTemplateName($name);
  public function setTemplatePath($path);
  public function configuration();
  public function display();
}

abstract class Sabel_Template_Engine implements Sabel_Template_EngineInterface
{
  protected
    $tplpath = null,
    $tplname = null;
    
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
}
