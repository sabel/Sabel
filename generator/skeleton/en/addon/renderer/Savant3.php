<?php

/**
 * Renderer_Savant3
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Savant3 extends Sabel_View_Renderer
{
  private $savant = null;
  
  public function initialize()
  {
    require_once ("Savant/Savant3.php");
    
    $this->savant = new Savant3();
    $this->savant->setExtract(true);
  }
  
  public function rendering($_tpl_contents, $_tpl_values, $_tpl_path = null)
  {
    $savant = $this->savant;
    
    if ($_tpl_path === null || !is_file($_tpl_path)) {
      $hash = $this->createHash($_tpl_contents);
      $_tpl_path = COMPILE_DIR_PATH . DS . $hash;
      file_put_contents($_tpl_path, $_tpl_contents);
    }
    
    $savant->setPath("template", dirname($_tpl_path));
    $_tpl_values["renderer"] = $this;
    $savant->assign($_tpl_values);
    
    return $savant->fetch(basename($_tpl_path));
  }
}
