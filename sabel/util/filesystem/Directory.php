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
class Sabel_Util_FileSystem_Directory extends Sabel_Util_FileSystem_Base
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
  
  public function mkdir($directory, $permission = 0744)
  {
    if (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DS . $directory;
    }
    
    if ($this->isDir($directory) || $this->isFile($directory)) {
      $message = "cannot create directory '{$directory}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->_mkdir($directory, $permission);
      return new Sabel_Util_FileSystem_Directory($directory);
    }
  }
  
  public function mkfile($file, $permission = 0744)
  {
    if (!$this->isAbsolutePath($file)) {
      $file = $this->path . DS . $file;
    }
    
    if ($this->isDir($file) || $this->isFile($file)) {
      $message = "cannot create directory '{$file}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->_mkfile($file, $permission);
      return new Sabel_Util_FileSystem_File($file);
    }
  }
  
  public function rmdir($directory = null)
  {
    if ($directory === null) {
      $directory = $this->path;
    } elseif (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DS . $directory;
    }
    
    if (!$this->isDir($directory)) {
      trigger_error("no such file or directory.", E_USER_WARNING);
    } elseif ($this->isFile($directory)) {
      trigger_error("'{$directory}': not a directory.", E_USER_WARNING);
    } else {
      $this->_rmdir($directory);
      rmdir($directory);
    }
  }
}
