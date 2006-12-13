<?php

/**
 * Sabel_View_Renerer_Class
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 * @copyright  2002-2006 Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Renderer_Simplate extends Sabel_View_Renderer
{
  const COMPILE_DIR = '/data/compiled/';
  const CACHE_DIR   = '/cache/';
  
  private $simplate = null;
  
  public function __construct()
  {
    $simplate = new simplate();
    $simplate->compile_dir  = RUN_BASE . self::COMPILE_DIR;
    $simplate->lazy_check   = true;
    
    $this->simplate = $simplate;
  }
  
  public function enableCache()
  {
    $simplate = $this->simplate;
    
    $simplate->caching = true;
    $simplate->cache_dir = RUN_BASE . self::CACHE_DIR;
    $simplate->cache_lifetime = 600;
  }
  
  public function rendering($path, $name, $values)
  {
    $simplate = $this->simplate;
    $simplate->template_dir = $path;
    
    foreach ($values as $k => $v) $simplate->assign($k, $v);
    
    return $simplate->fetch($name);
  }
}
