<?php

class Sabel_Template_Director_Default implements Sabel_Template_Director_Interface
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