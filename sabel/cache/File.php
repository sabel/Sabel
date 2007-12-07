<?php

/**
 * cache to file
 *
 * @category   Cache
 * @package    org.sabel.cache
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Cache_File implements Sabel_Cache_Interface
{
  private static $instance = null;
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  public function read($key)
  {
    $path = $this->getPath($key);
    
    if (is_readable($path)) {
      return unserialize(file_get_contents($path));
    } else {
      return null;
    }
  }
  
  public function write($key, $value, $timeout = 600, $comp = false)
  {
    file_put_contents($this->getPath($key), serialize($value));
  }
  
  public function delete($key)
  {
    $path = $this->getPath($key);
    if (is_file($path)) unlink($path);
  }
  
  protected function getPath($key)
  {
    return CACHE_DIR_PATH . DS . $key;
  }
}
