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
class Sabel_View_Renderer_Class extends Sabel_View_Renderer
{
  const REPLACE_PAT
    = '/<\?php([a-z=]*)%s([a-z=]*)\s*([^?;]+)([^?]+)\?>/';
    
  const ISSET_REPLACE = '<?php $3 = (isset($3)) ? $3 : "" ?><?php$1$2 $3$4 ?>';
  const H_REPLACE     = '<?php$1$2= htmlspecialchars($3)$4 ?>';
  const DUMP_REPLACE  = '<pre><?php$1$2 var_dump($3)$4 ?></pre>';
  const NL2BR_REPLACE = '<?php= nl2br($3)$4 ?>';
  
  private $isCache = false;
  
  public function enableCache()
  {
    $this->isCache = true;
  }
  
  public function rendering($sbl_tpl_path, $sbl_tpl_name, $sbl_tpl_values)
  {
    if ($this->isCache) {
      $sbl_tpl_cache_path = $this->getCacheFilePath($sbl_tpl_path, $sbl_tpl_name);
      if (is_readable($sbl_tpl_cache_path) && filemtime($sbl_tpl_cache_path) > time() - 600) {
        return file_get_contents($sbl_tpl_cache_path);
      }
    }
    
    $sbl_tpl_contents = '';
    $sbl_tpl_file_path = $sbl_tpl_path . $sbl_tpl_name;
    $sbl_cache_path    = $this->getCompileFilePath($sbl_tpl_path, $sbl_tpl_name);
    
    if (is_readable($sbl_tpl_file_path) && (!is_readable($sbl_cache_path) 
        || filemtime($sbl_tpl_file_path) > filemtime($sbl_cache_path))) {
      $sbl_tpl_contents = file_get_contents($sbl_tpl_file_path);
      
      $sbl_tpl_contents = str_replace('<?', '<?php', $sbl_tpl_contents);
      
      $sbl_tpl_contents = preg_replace(sprintf(self::REPLACE_PAT, 'i'), self::ISSET_REPLACE, $sbl_tpl_contents);
      $sbl_tpl_contents = preg_replace(sprintf(self::REPLACE_PAT, 'h'), self::H_REPLACE,     $sbl_tpl_contents);
      $sbl_tpl_contents = preg_replace(sprintf(self::REPLACE_PAT, 'v'), self::DUMP_REPLACE,  $sbl_tpl_contents);
      $sbl_tpl_contents = preg_replace(sprintf(self::REPLACE_PAT, 'n'), self::NL2BR_REPLACE, $sbl_tpl_contents);

      $sbl_tpl_contents = str_replace('<?php=', '<?php echo',  $sbl_tpl_contents);
      
      if (ENVIRONMENT !== DEVELOPMENT) {
        $sbl_replace_pattern = '/a\(\'([^\'?]+)\'(?:[,\s]+([^,?\s]+)[,\s]*([^?\s]+)?)?\) \?>/';
        $sbl_tpl_contents = preg_replace_callback($sbl_replace_pattern, '_repA', $sbl_tpl_contents);
        if ($this->trim) {
          $sbl_tpl_contents = explode("\n",     $sbl_tpl_contents);
          $sbl_tpl_contents = array_map('trim', $sbl_tpl_contents);
          $sbl_tpl_contents = implode('',       $sbl_tpl_contents);
        }
      }
      
      $this->saveCompileFile($sbl_tpl_path, $sbl_tpl_name, $sbl_tpl_contents);
    }
    
    extract($sbl_tpl_values, EXTR_SKIP);
    
    ob_start();
    if (is_readable($sbl_cache_path)) include ($sbl_cache_path);
    $sbl_tpl_contents = ob_get_clean();
    if ($this->isCache) $this->saveCacheFile($sbl_tpl_path, $sbl_tpl_name, $sbl_tpl_contents);
    
    return $sbl_tpl_contents;
  }
  
  private function saveCacheFile($sbl_tpl_path, $sbl_tpl_name, $sbl_tpl_contents)
  {
    $sbl_cache_path = $this->getCacheFilePath($sbl_tpl_path, $sbl_tpl_name);
    file_put_contents($sbl_cache_path, $sbl_tpl_contents);
  }
  
  private function saveCompileFile($sbl_tpl_path, $sbl_tpl_name, $sbl_tpl_contents)
  {
    $sbl_cache_path = $this->getCompileFilePath($sbl_tpl_path, $sbl_tpl_name);
    file_put_contents($sbl_cache_path, $sbl_tpl_contents);
  }
  
  private function getCacheFilePath($sbl_tpl_path, $sbl_tpl_name)
  {
    return RUN_BASE . self::CACHE_DIR . md5($sbl_tpl_path) . $sbl_tpl_name;
  }
  
  private function getCompileFilePath($sbl_tpl_path, $sbl_tpl_name)
  {
    return RUN_BASE . self::COMPILE_DIR . md5($sbl_tpl_path) . $sbl_tpl_name;
  }
}

function _repA($matches)
{
  $a = $matches[2];
  if ($a{0} === '_') {
    $a = eval('return '.$a.';');
  }
  return '"'.a($matches[1], $a).'" ?>';
}
