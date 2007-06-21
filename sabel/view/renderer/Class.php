<?php

/**
 * Sabel_View_Renerer_Class
 *
 * @category   Template
 * @package    org.sabel.template.engine
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 *             Mori Reo <mori.reo@gmail.com>
 *
 * @copyright  2002-2006 Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
final class Sabel_View_Renderer_Class extends Sabel_View_Renderer
{
  public function rendering($sbl_template, &$sbl_tpl_values)
  {
    $hash = md5(substr($sbl_template, 0, 256));
    $this->makeCompileFile($sbl_template, $hash);
        
    $this->initializeValues($hash, $sbl_tpl_values);
    extract($sbl_tpl_values, EXTR_OVERWRITE);
    ob_start();
    include ($this->getCompileFilePath($hash));
    return ob_get_clean();
  }
  
  private final function initializeValues($hash, &$sbl_tpl_values)
  {
    $buf = file_get_contents($this->getCompileFilePath($hash));
    if (preg_match_all('/\$([\w]+)/', $buf, $matches)) {
      $buf = array();
      $filtered = array_filter($matches[1], '_sbl_internal_remove_this');
      foreach ($filtered as $key => $val) {
        $buf[$val] = null;
      }
      $sbl_tpl_values = array_merge($buf, $sbl_tpl_values);
    }
  }
  
  private final function makeCompileFile($template, $hash)
  {
    if (ENVIRONMENT === PRODUCTION) {
      if (is_readable($this->getCompileFilePath($hash))) return;
    }
    
    $r = '/<\?(=)?\s([^?;\s]+)([^?]+)\?>/';
    $template = preg_replace_callback($r, '_sbl_tpl_pipe_to_func', $template);
    $template = str_replace('<?=', '<? echo', $template);
    $template = preg_replace('/<\?(?!xml)/', '<?php', $template);
    
    if (defined("URI_IGNORE")) {
      $images = "jpg|gif|bmp|tiff|png|swf|jpeg|css|js";
      $quote = '"|\'';
      $pat = "@(({$quote})/[\w-_/.]*([.]({$images}))?({$quote}))@";
      $template = preg_replace($pat, '<?= linkto($1) ?>', $template);
    }
    
    if (ENVIRONMENT !== DEVELOPMENT && $this->trim) {
      $template = $this->trimContents($template);
    }
    
    $this->saveCompileFile(RUN_BASE . "/data/compile/", $hash, $template);
  }
  
  private final function checkAndTrimContents($contents)
  {
    if (strpos($contents, '<script') === false) {
      $contents = explode("\n",     $contents);
      $contents = array_map('trim', $contents);
      $contents = implode('',       $contents);
    } else {
      $pat = '@(.*)(<script [^>]+>.*</script>)(.*)@si';
      $callback = array($this, 'trimContents');
      $contents = preg_replace_callback($pat, $callback, $contents);
    }
    
    return $contents;
  }
  
  private final function trimContents($contents)
  {
    if (is_string($contents)) {
      $contents = $this->checkAndTrimContents($contents);
    } elseif (is_array($contents)) {
      $head   = $this->checkAndTrimContents($contents[1]);
      $script = $contents[2];
      $foot   = $this->checkAndTrimContents($contents[3]);
      
      $contents = $head . "\n" . $script . "\n" . $foot;
    }
    return $contents;
  }
  
  private final function saveCompileFile($path, $name, $compiled)
  {
    file_put_contents(RUN_BASE . self::COMPILE_DIR . $name, $compiled);
  }
  
  private final function getCompileFilePath($name)
  {
    return RUN_BASE . self::COMPILE_DIR . $name;
  }
}

function _sbl_tpl_pipe_to_func($matches)
{
  $pre   = trim($matches[1]);
  $value = $matches[2];
  $post  = rtrim($matches[3]);
  
  if (strpos($value, '|') !== false) {
    $functions = explode('|', $value);
    $value = array_shift($functions);
    $lamdaBody = 'return (is_string($val)) ? "\"".$val."\"" : $val;';
    $lamda = create_function('$val', $lamdaBody);
    foreach ($functions as $function) {
      $params = '';
      if (strpos($function, ':') !== false) {
        $params   = explode(':', $function);
        $function = array_shift($params);
        $params   = array_map($lamda, $params);
        $params   = ', ' . implode(', ', $params);
      }
      $value = "$function($value$params)";
    }
  }
  
  return "<?${pre} ${value}${post} ?>";
}

function _sbl_internal_remove_this($arg)
{
  return ($arg !== '$this');
}
