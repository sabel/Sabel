<?php

/**
 * Sabel_Util_VariableCache
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_VariableCache
{
  private static $instances = array();
  
  private $filePath = array();
  private $data = array();
  
  private function __construct()
  {
    
  }
  
  public static function create($_filePath)
  {
    if (isset(self::$instances[$_filePath])) {
      return self::$instances[$_filePath];
    }
    
    $_path = self::getPath($_filePath);
    
    if (is_readable($_path)) {
      include ($_path);
      $vars = get_defined_vars();
      unset($vars["_path"]);
      unset($vars["_filePath"]);
      $vars = $vars;
    } else {
      $vars = array();
    }
    
    $instance = new self();
    $instance->filePath = $_filePath;
    $instance->data = $vars;
    
    self::$instances[$_filePath] = $instance;
    return $instance;
  }
  
  public function read($key)
  {
    if (isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      return null;
    }
  }
  
  public function write($key, $value)
  {
    $this->data[$key] = $value;
  }
  
  public function delete($key)
  {
    unset($this->data[$key]);
  }
  
  public function save()
  {
    $contents = array();
    
    foreach ($this->data as $var => $value) {
      $contents[] = "\${$var}=" . $this->toPhpString($value) . ";";
    }
    
    $contents = "<?php" . PHP_EOL . implode(PHP_EOL, $contents);
    file_put_contents($this->getPath($this->filePath), $contents);
  }
  
  protected function toPhpString($value)
  {
    if (is_array($value)) {
      return $this->createArray($value);
    } elseif (is_bool($value)) {
      return ($value) ? "true" : "false";
    } elseif (is_string($value)) {
      return "'{$value}'";
    } else {
      return $value;
    }
  }
  
  protected function createArray($value)
  {
    $array = array();
    foreach ($value as $k => $v) {
      $key = (is_int($k)) ? $k : "'{$k}'";
      $array[] = "{$key}=>" . $this->toPhpString($v);
    }
    
    return "array(" . implode(",", $array) . ")";
  }
  
  private static function getPath($key)
  {
    return CACHE_DIR_PATH . DS . $key . PHP_SUFFIX;
  }
}
