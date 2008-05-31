<?php

/**
 * Renderer_Simplate
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Simplate extends Sabel_View_Renderer
{
  private $simplate = null;
  
  public function initialize()
  {
    $simplate = new simplate();
    $simplate->compile_dir  = COMPILE_DIR_PATH . DS;
    $simplate->lazy_check   = true;
    
    if (ENVIRONMENT === PRODUCTION) {
      $simplate->caching   = true;
      $simplate->cache_dir = CACHE_DIR_PATH . DS;
      $simplate->cache_lifetime = 600;
    }
    
    $this->simplate = $simplate;
  }
  
  public function rendering($_tpl_contents, $_tpl_values, $_tpl_path = null)
  {
    $simplate = $this->simplate;
    
    if ($_tpl_path === null || !is_file($_tpl_path)) {
      $hash = $this->createHash($_tpl_contents);
      $_tpl_path = COMPILE_DIR_PATH . DS . $hash;
      file_put_contents($_tpl_path, $_tpl_contents);
    }
    
    $simplate->template_dir = dirname($_tpl_path);
    
    foreach ($_tpl_values as $k => $v) {
      $simplate->assign($k, $v);
    }
    
    return $simplate->fetch(basename($_tpl_path));
  }
}
