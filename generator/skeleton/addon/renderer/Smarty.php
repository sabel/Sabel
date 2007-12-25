<?php

/**
 * Renderer_Smarty
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Smarty extends Sabel_View_Renderer
{
  private $smarty = null;
  
  public function initialize()
  {
    require_once ("Smarty/Smarty.class.php");
    
    $smarty = new Smarty();
    $smarty->compile_dir = COMPILE_DIR_PATH . DS;
    
    if (ENVIRONMENT === PRODUCTION) {
      $smarty->caching   = true;
      $smarty->cache_dir = CACHE_DIR_PATH . DS;
      $simplate->cache_lifetime = 600;
    }
    
    $this->smarty = $smarty;
  }
  
  public function rendering($_tpl_contents, $_tpl_values, $_tpl_path = null)
  {
    $smarty = $this->smarty;
    
    if ($_tpl_path === null) {
      $hash = $this->createHash($_tpl_contents);
      $_tpl_path = COMPILE_DIR_PATH . DS . $hash;
      file_put_contents($_tpl_path, $_tpl_contents);
    }
    
    if (!$smarty->is_cached($_tpl_path)) {
      $smarty->assign($_tpl_values);
    }
    
    return $smarty->fetch($_tpl_path);
  }
}
