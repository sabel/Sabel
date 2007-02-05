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
class Sabel_Cache_File
{
  private $dir = "";
  private static $instance = null;
  
  public function __construct($dir = null)
  {
    if ($dir === null) {
      $this->dir = RUN_BASE . "/cache";
    } else {
      $this->dir = $dir;
    }
  }
  
  public static function create()
  {
    if (self::$instance === null) self::$instance = new self();
    return self::$instance;
  }
  
  public function read($key)
  {
    $path = $this->getPath($key);
    if ($this->isReadable($path)) {
      file_get_contents($path);
    } else {
      return null;
    }
  }
  
  public function isReadable($key)
  {
    return is_readable($this->getPath($key));
  }
  
  public function write($key, $value)
  {
    file_put_contents($this->getPath($key), $value);
  }
    
  public function append($value)
  {
    $fp = fopen($this->path, 'a+');
    if (!$fp) return null;
    $this->writeToFile($fp, $value);
    fclose($fp);
  }
  
  protected function getPath($key)
  {
    return $this->dir . "/" . $key;
  }
}