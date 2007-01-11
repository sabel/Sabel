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
  const ISSET_PAT     = '/<\?i\s*(([^?;|]+)[^?;]*)([^?]+)\?>/';
  const ISSET_REPLACE = '<? if (isset($2)) echo $1$3 ?>';
  const PIPE_PAT      = '/<\?(.+echo )([^?;\s]+)([^?]+)\?>/';
  
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
    
    $this->makeCompileFile($sbl_tpl_path, $sbl_tpl_name);
    
    $sbl_compile_path  = $this->getCompileFilePath($sbl_tpl_path, $sbl_tpl_name);
    
    ob_start();
    $buf = file_get_contents($sbl_compile_path);
    if (preg_match_all('/(\$[\w]+)/', $buf, $matches)) {
      foreach ($matches[1] as $key) {
        if ($key !== '$this') eval("$key = null;");
      }
    }
    extract($sbl_tpl_values, EXTR_OVERWRITE);
    include ($sbl_compile_path);
    $sbl_tpl_contents = ob_get_clean();
    if ($this->isCache) $this->saveCacheFile($sbl_tpl_path, $sbl_tpl_name, $sbl_tpl_contents);
    
    return $sbl_tpl_contents;
  }
  
  private function makeCompileFile($path, $name)
  {
    $filepath    = $path . $name;
    $compilepath = $this->getCompileFilePath($path, $name);
    if (is_readable($compilepath) && filemtime($filepath) < filemtime($compilepath)) return;

    $contents = file_get_contents($path . $name);
    
    $contents = preg_replace(self::ISSET_PAT, self::ISSET_REPLACE, $contents);
    $contents = str_replace('<?=', '<? echo', $contents);
    $contents = preg_replace_callback(self::PIPE_PAT, array(&$this, 'pipeToFunc'), $contents);
    $contents = str_replace('<?',  '<?php',   $contents);
    
    if (ENVIRONMENT !== DEVELOPMENT) {
      if ($this->trim) {
        $contents = explode("\n",     $contents);
        $contents = array_map('trim', $contents);
        $contents = implode('',       $contents);
      }
    }
    $this->saveCompileFile($path, $name, $contents);
  }
  
  private function pipeToFunc($matches)
  {
    $pre   = rtrim($matches[1]);
    $value = $matches[2];
    $post  = rtrim($matches[3]);
    
    if (strpos($value, '|') !== false) {
      $functions = explode('|', $value);
      $value = array_shift($functions);
      foreach ($functions as $function) {
        $params = '';
        if (strpos($function, ':') !== false) {
          $params   = explode(':', $function);
          $function = array_shift($params);
          $params   = array_map(create_function('$val', 'return (is_string($val)) ? "\'".$val."\'" : $val;'), $params);
          $params   = ', ' . implode(', ', $params);
        }
        $value = "$function($value$params)";
      }
    }
    
    return "<?${pre} ${value}${post} ?>";
  }
  
  private function saveCacheFile($path, $name, $contents)
  {
    $sbl_cache_path = $this->getCacheFilePath($path, $name);
    file_put_contents($sbl_cache_path, $contents);
  }
  
  private function saveCompileFile($path, $name, $contents)
  {
    $sbl_cache_path = $this->getCompileFilePath($path, $name);
    file_put_contents($sbl_cache_path, $contents);
  }
  
  private function getCacheFilePath($path, $name)
  {
    return RUN_BASE . self::CACHE_DIR . md5($path) . $name;
  }
  
  private function getCompileFilePath($path, $name)
  {
    return RUN_BASE . self::COMPILE_DIR . md5($path) . $name;
  }
}
