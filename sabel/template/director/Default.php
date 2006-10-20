<?php

/**
 * Sabel_Template_Director_Default
 *
 * @category   Template
 * @package    org.sabel.template.director
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Template_Director_Default implements Sabel_Template_Director_Interface
{
  protected $destination;
  
  public function __construct($destination)
  {
    $this->destination = $destination;
  }
  
  public function decidePath()
  {
    return $this->getPath();
  }
  
  public function decideName()
  {
    return $this->getName();
  }
  
  public function getFullPath()
  {
    return $this->getPath() . $this->getName();
  }
  
  protected function getPath()
  {
    $tplpath  = RUN_BASE;
    $tplpath .= Sabel_Core_Const::MODULES_DIR;
    $tplpath .= $this->destination->module . '/';
    $tplpath .= Sabel_Core_Const::TEMPLATE_DIR;
    return $tplpath;
  }
  
  protected function getName()
  {
    // make name string of template such as "controller.method.tpl"
    $tplname  = $this->destination->controller;
    $tplname .= Sabel_Core_Const::TEMPLATE_NAME_SEPARATOR;
    $tplname .= $this->destination->action;
    $tplname .= Sabel_Core_Const::TEMPLATE_POSTFIX;
    return $tplname;
  }
}