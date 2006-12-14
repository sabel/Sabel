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
    = '/<\?php([a-z]*)%s([a-z=]*)\s*([^?;]+)([^?]+)\?>/';
    
  const H_REPLACE     = '<?php$1$2= htmlspecialchars($3)$4 ?>';
  const DUMP_REPLACE  = '<pre><?php$1$2 var_dump($3)$4 ?></pre>';
  const NL2BR_REPLACE = '<?php= nl2br($3)$4 ?>';
  
  private $isCache = false;
  
  public function enableCache()
  {
    $this->isCache = true;
  }
  
  public function rendering($path, $name, $values)
  {
    if ($this->isCache) {
      $cachePath = $this->getCacheFilePath($path, $name);
      if (is_readable($cachePath) && filemtime($cachePath) > time() - 600) {
        return file_get_contents($cachePath);
      }
    }
    
    $contents = '';
    $filepath = $path . $name;
    $cpath    = $this->getCompileFilePath($path, $name);
    
    if (is_readable($filepath) && (!is_readable($cpath) 
        || filemtime($filepath) > filemtime($cpath))) {
      $contents = file_get_contents($filepath);
      
      $contents = str_replace('<?', '<?php', $contents);
      
      $contents = preg_replace(sprintf(self::REPLACE_PAT, 'h'), self::H_REPLACE,     $contents);
      $contents = preg_replace(sprintf(self::REPLACE_PAT, 'v'), self::DUMP_REPLACE,  $contents);
      $contents = preg_replace(sprintf(self::REPLACE_PAT, 'n'), self::NL2BR_REPLACE, $contents);

      $contents = str_replace('<?php=', '<?php echo',  $contents);
      
      if (ENVIRONMENT !== DEVELOPMENT) {
        $rep = '/a\(\'([^\'?]+)\'(?:[,\s]+([^,?\s]+)[,\s]*([^?\s]+)?)?\) \?>/';
        $contents = preg_replace_callback($rep, '_repA', $contents);
        if ($this->trim) {
          $contents = explode("\n",     $contents);
          $contents = array_map('trim', $contents);
          $contents = implode('',       $contents);
        }
      }
      
      $this->saveCompileFile($path, $name, $contents);
    }
    
    extract($values, EXTR_SKIP);
    
    ob_start();
    if (is_readable($cpath)) include ($cpath);
    $contents = ob_get_clean();
    if ($this->isCache) $this->saveCacheFile($path, $name, $contents);
    
    return $contents;
  }
  
  private function saveCacheFile($path, $name, $contents)
  {
    $cpath = $this->getCacheFilePath($path, $name);
    file_put_contents($cpath, $contents);
  }
  
  private function saveCompileFile($path, $name, $contents)
  {
    $cpath = $this->getCompileFilePath($path, $name);
    file_put_contents($cpath, $contents);
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

function _repA($matches)
{
  $a = $matches[2];
  if ($a{0} === '_') {
    $a = eval('return '.$a.';');
  }
  return '"'.a($matches[1], $a).'" ?>';
}
