<?php

/**
 * Sabel_Template_Engine_Savant
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Template_Engine_Savant
{
  private $savant  = null;
  
  public function __construct()
  {
    require_once('Savant3/Savant3.php');
    $this->savant = new Savant3();
  }
  
  public function assign($key, $value)
  {
    $this->savant->assign($key, $value);
  }
  
  public function retrieve()
  {
    $fullpath = $this->getTemplateFullPath();
    
    if (file_exists($fullpath)) {
      return $this->savant->fetch($fullpath);
    } else {
      // @todo Exception handling.
    }
  }
  
  public function configuration()
  {
  }
  
  public function display()
  {
    $path = $this->getTemplateFullPath();
    if (!is_file($path))
      throw new SabelException("template isn't found: " . "'".$path."'");
      
    $this->savant->display($path);
  }
}