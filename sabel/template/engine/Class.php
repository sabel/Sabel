<?php

/**
 * Sabel_Template_Engine_Class
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 * @copyright  2002-2006 Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Template_Engine_Class extends Sabel_Template_Engine
{
  const COMPILE_DIR = '/data/compiled/';
  
  const H_PAT 
    = '/<\?php(n)?h([a-z=])*[[:blank:]]*([^?; ]+)[; \t]*(.*)?[; \t]*\?>/';
    
  const DUMP_PAT
    = '/<\?php(n)?v([a-z=])*[[:blank:]]*([^?; ]+)[; \t]*(.*)?[; \t]*\?>/';
    
  const NL2BR_PAT = 
    '/<\?phpn([a-z=])*[[:blank:]]*([^?; ]+)[; \t]*(.*)?[; \t]*\?>/';
    
  const H_REPLACE     = '<?php$1$2= htmlspecialchars($3); $4 ?>';
  const DUMP_REPLACE  = '<pre><?php$1$2 var_dump($3); $4 ?></pre>';
  const NL2BR_REPLACE = '<?php= nl2br($2); $3 ?>';
  
  public function configuration()
  {
  }

  public function retrieve()
  {
    $contents  = '';
    $pageCache = false;

    $filepath = $this->getTemplateFullPath();
    $cpath    = $this->getCompileFilePath();

    if (is_file($filepath) && (!is_readable($cpath) 
        || filemtime($filepath) > filemtime($cpath))) {
      $contents = file_get_contents($filepath);

      $contents = str_replace('<?', '<?php', $contents);
      
      $contents = preg_replace(self::H_PAT,     self::H_REPLACE,     $contents);
      $contents = preg_replace(self::DUMP_PAT,  self::DUMP_REPLACE,  $contents);
      $contents = preg_replace(self::NL2BR_PAT, self::NL2BR_REPLACE, $contents);

      $contents = str_replace('<?php=', '<?php echo',  $contents);
      
      if (ENVIRONMENT !== 'development' && $this->trim) {
        $contents = explode("\n",     $contents);
        $contents = array_map('trim', $contents);
        $contents = implode('',       $contents);
      }
      
      $this->saveCompileFile($contents);
    }

    if (count(self::$attributes) != 0) extract(self::$attributes, EXTR_SKIP);
    
    ob_start();
    if (is_file($cpath)) include($cpath);
    
    $this->getHelperPath();
    $contents = ob_get_clean();
    if ($pageCache) $this->saveCompileFile($contents);

    return $contents;
  }

  private function saveCompileFile($contents)
  {
    $cpath = $this->getCompileFilePath();
    $fp = fopen($cpath, 'w');
    fwrite($fp, $contents);
    fclose($fp);
  }

  private function getCompileFilePath()
  {
    return RUN_BASE . self::COMPILE_DIR . md5($this->tplpath) . $this->tplname;
  }
}
