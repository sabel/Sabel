<?php

/**
 * Renderer_Sabel
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Hamanaka Kazuhiro <hamanaka.kazuhiro@sabel.jp>
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Sabel extends Sabel_View_Renderer
{
  protected $replacer = null;
  
  public function __construct()
  {
    $baseDir = dirname(__FILE__) . DS . "sabel" . DS;
    Sabel::fileUsing($baseDir . "Replacer.php", true);
    Sabel::fileUsing($baseDir . "Parser.php",   true);
    Sabel::fileUsing($baseDir . "Element.php",  true);
    
    $this->replacer = new Renderer_Sabel_Replacer();
  }
  
  public function rendering($sbl_template, $sbl_tpl_values, $sbl_tpl_path = null)
  {
    $hash = $this->createHash($sbl_template);
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
    
    $template = $this->replacer->execute($template);
    
    $r = '/<\?(=)?\s(.+)\s\?>/U';
    $template = preg_replace_callback($r, '_sbl_tpl_pipe_to_func', $template);
    
    if (defined("URI_IGNORE")) {
      $images = "jpg|gif|bmp|tiff|png|swf|jpeg|css|js";
      $quote = '"|\'';
      $pat = "@(({$quote})/[\w-_/.]*(\.({$images}))({$quote}))@";
      $template = preg_replace($pat, '"<?= linkto($1) ?>"', $template);
    }
    
    $template = str_replace('<?=', '<? echo', $template);
    $template = preg_replace('/<\?(?!xml)/', '<?php', $template);
    $template = str_replace('<?xml', '<<?php echo "?" ?>xml', $template);
    
    file_put_contents(COMPILE_DIR_PATH . DS . $hash, $template);
  }
  
  private final function getCompileFilePath($name)
  {
    return COMPILE_DIR_PATH . DS . $name;
  }
}

function _sbl_tpl_pipe_to_func($matches)
{
  $pre    = trim($matches[1]);
  $values = explode(" ", $matches[2]);
  
  foreach ($values as &$value) {
    if ($value === "||") continue;
    if (strpos($value, "|") !== false) {
      $functions = explode("|", $value);
      $value = array_shift($functions);
      $lamdaBody = 'return (is_string($val)) ? "\"".$val."\"" : $val;';
      $lamda = create_function('$val', $lamdaBody);
      
      foreach ($functions as $function) {
        $params = "";
        if (strpos($function, ":") !== false) {
          $params   = explode(":", $function);
          $function = array_shift($params);
          $params   = array_map($lamda, $params);
          $params   = ", " . implode(", ", $params);
        }
        
        $value = "$function($value$params)";
      }
    }
  }
  
  $value = implode(" ", $values);
  return "<?${pre} ${value} ?>";
}

function _sbl_internal_remove_this($arg)
{
  return ($arg !== '$this');
}
