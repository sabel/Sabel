<?php

/**
 * Sabel_View_Renderer_PHP
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer_PHP extends Sabel_View_Renderer
{
  public function rendering($sbl_tpl_path, $sbl_tpl_name, $sbl_tpl_values)
  {
    Sabel::using("Sabel_View_Helper_Prototype");
    $page = new Sabel_View_Helper_Prototype_Page();
    
    extract($sbl_tpl_values, EXTR_OVERWRITE);
    // ob_start();
    include($sbl_tpl_path . $sbl_tpl_name);
    $content = $page->toJavaScript();
    // $content = ob_get_clean();
    return $content;
  }
}