<?php

/**
 * Sabel_Util_FileSystem_Directory
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_FileSystem_Directory extends Sabel_Object
{
  protected $path = "";
  
  public function __construct($path)
  {
    $this->path = $path;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function getDirectoryNames()
  {
    clearstatcache();
    
    $dirs = array();
    $path = $this->path;
    
    foreach (scandir($path) as $item) {
      if ($item === "." || $item === "..") continue;
      if (is_dir($path . DS . $item)) $dirs[] = $item;
    }
    
    return $dirs;
  }
  
  public function getFileNames()
  {
    clearstatcache();
    
    $files = array();
    $path  = $this->path;
    
    foreach (scandir($path) as $item) {
      if (is_file($path . DS . $item)) $files[] = $item;
    }
    
    return $files;
  }
  
  public function mkdir($directory, $permission = 0755)
  {
    clearstatcache();
    
    $directory = $this->_getPath($directory);
    
    if (is_dir($directory) || is_file($directory)) {
      $message = "cannot create directory '{$directory}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      mkdir($directory);
      chmod($directory, $permission);
    }
  }
  
  public function mkfile($file, $permission = 0755)
  {
    clearstatcache();
    
    $file = $this->_getPath($file);
    
    if (is_dir($file) || is_file($file)) {
      $message = "cannot create directory '{$file}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      file_put_contents($file, "");
      chmod($file, $permission);
    }
  }
  
  public function remove($path = null)
  {
    if ($path === null) {
      rmdir($this->path);
    } else {
      clearstatcache();
      $path = $this->_getPath($path);
      
      if (is_file($path)) {
        unlink($path);
      } elseif (is_dir($path)) {
        rmdir($path);
      } else {
        trigger_error("no such file or directory.", E_USER_WARNING);
      }
    }
  }
  
  protected function _getPath($path)
  {
    return $this->path . DS . $path;
  }
}
