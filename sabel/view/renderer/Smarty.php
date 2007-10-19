<?php

/**
 * Sabel_View_Renderer_Smarty
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer_Smarty extends Sabel_View_Renderer
{
  private $smarty  = null;
  
  public function __construct()
  {
    $this->smarty = new Smarty();
    $this->smarty->compile_dir = RUN_BASE . DS . TPL_COMPILE_DIR . DS;
  }
  
  public function enableCache()
  {
    $smarty = $this->smarty;
    
    $smarty->caching = true;
    $smarty->cache_dir = RUN_BASE . DS . TPL_CACHE_DIR . DS;
    $smarty->cache_lifetime = 600;
  }
  
  public function rendering($path, $name, $values)
  {
    $smarty = $this->smarty;
    $smarty->template_dir = $path;
    
    if (!$smarty->is_cached($name))
      foreach ($values as $k => $v) $smarty->assign($k, $v);
    
    return $smarty->fetch($name);
  }
}
