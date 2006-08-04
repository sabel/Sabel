<?php

interface SabelTemplateDirector
{
  public function decidePath();
  public function decideName();
}

class TemplateDirectorFactory
{
  public static function create($request = null, $destination)
  {
    $classPath  = Sabel_Core_Const::MODULES_DIR . $destination->module;
    $classPath .= '/extensions/CustomTemplateDirector.php';
    
    $commonsPath  = Sabel_Core_Const::COMMONS_DIR;
    $commonsPath .= '/extensions/CustomTemplateDirector.php';
    
    if (is_file($classPath)) {
      require_once($classPath);
      return new CustomTemplateDirector($destination);
    } else if (is_file($commonsPath)) {
      require_once($commonsPath);
      return new CustomTemplateDirector($destination);
    } else {
      return new DefaultTemplateDirector($destination);
    }
  }
}

class DefaultTemplateDirector implements SabelTemplateDirector
{
  protected $destination;
  
  public function __construct($destination)
  {
    $this->destination = $destination;
  }
  
  protected function getPath()
  {
    $tplpath  = RUN_BASE;
    $tplpath .= Sabel_Core_Const::MODULES_DIR;
    $tplpath .= $this->destination->module . '/';
    $tplpath .= Sabel_Core_Const::TEMPLATE_DIR;
    return $tplpath;
  }
  
  public function decidePath()
  {
    return $this->getPath();
  }
  
  protected function getName()
  {
    // makeing template name string such as "controller.method.tpl"
    $tplname  = $this->destination->controller;
    $tplname .= Sabel_Core_Const::TEMPLATE_NAME_SEPARATOR;
    $tplname .= $this->destination->action;
    $tplname .= Sabel_Core_Const::TEMPLATE_POSTFIX;
    return $tplname;
  }
  
  public function decideName()
  {
    return $this->getName();
  }
  
  public function getFullPath()
  {
    return $this->getPath() . $this->getName();
  }
}