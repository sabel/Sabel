<?php

/**
 * cache to file
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_File implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  protected $dir = "";
  
  private function __construct($dir)
  {
    $this->dir = ($dir === "") ? CACHE_DIR_PATH : $dir;
  }
  
  public static function create($dir = "")
  {
    if (self::$instance === null) {
      self::$instance = new self($dir);
    }
    
    return self::$instance;
  }
  
  public function read($key)
  {
    $path = $this->getPath($key);
    
    if (is_readable($path)) {
      $data = unserialize(file_get_contents($path));
      
      if ($data["timeout"] !== 0 && time() >= $data["timeout"]) {
        $this->delete($key);
      } else {
        return $data["value"];
      }
    } else {
      return null;
    }
  }
  
  public function write($key, $value, $timeout = 0)
  {
    $data = array("value" => $value);
    
    if ($timeout !== 0) {
      $timeout = time() + $timeout;
    }
    
    $data["timeout"] = $timeout;
    file_put_contents($this->getPath($key), serialize($data));
  }
  
  public function delete($key)
  {
    $path = $this->getPath($key);
    if (is_file($path)) unlink($path);
  }
  
  protected function getPath($key)
  {
    return $this->dir . DS . $key;
  }
}
