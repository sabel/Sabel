<?php

/**
 * Sabel_View_Renderer_Savant
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer_Savant extends Sabel_View_Renderer
{
  private $savant  = null;
  
  public function __construct()
  {
    require_once('Savant3/Savant3.php');
    $this->savant = new Savant3();
  }
  
  public function rendering($path, $name, $values)
  {
    $fullpath = $this->getTemplateFullPath();
    
    if (file_exists($fullpath)) {
      return $this->savant->fetch($fullpath);
    } else {
      // @todo Exception handling.
    }
  }
}