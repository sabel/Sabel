<?php

/**
 * Sabel_Util_FileSystem
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_FileSystem extends Sabel_Util_FileSystem_Base
{
  public function __construct($base = "")
  {
    if ($base === "") {
      $this->path = (DIRECTORY_SEPARATOR === '\\') ? "C:\\" : "/";
    } else {
      $this->path = realpath($base);
    }
  }
  
  public function pwd()
  {
    return $this->path;
  }
  
  public function cd($path)
  {
    clearstatcache();
    
    if (!$this->isAbsolutePath($path)) {
      $path = $this->path . DIRECTORY_SEPARATOR . $path;
    }
    
    if (is_dir($path)) {
      $this->path = realpath($path);
    } else {
      trigger_error("no such file or directory.", E_USER_WARNING);
    }
    
    return $this;
  }
  
  public function ls($path = null)
  {
    if ($path === null) {
      $path = $this->path;
    } elseif (!$this->isAbsolutePath($path)) {
      $path = $this->path . DIRECTORY_SEPARATOR . $path;
    }
    
    $items = array();
    foreach (scandir($path) as $item) {
      if ($item === "." || $item === "..") continue;
      $items[] = $item;
    }
    
    return $items;
  }
  
  public function getDirectory($path)
  {
    if (!$this->isAbsolutePath($path)) {
      $path = $this->path . DIRECTORY_SEPARATOR . $path;
    }
    
    if ($this->isDir($path)) {
      return new self($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getFile($path)
  {
    if (!$this->isAbsolutePath($path)) {
      $path = $this->path . DIRECTORY_SEPARATOR . $path;
    }
    
    if ($this->isFile($path)) {
      return new Sabel_Util_FileSystem_File($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getDirectoryNames()
  {
    clearstatcache();
    
    $dirs = array();
    $path = $this->path;
    
    foreach (scandir($path) as $item) {
      if ($item === "." || $item === "..") continue;
      if (is_dir($path . DIRECTORY_SEPARATOR . $item)) $dirs[] = $item;
    }
    
    return $dirs;
  }
  
  public function getFileNames()
  {
    clearstatcache();
    
    $files = array();
    $path  = $this->path;
    
    foreach (scandir($path) as $item) {
      if (is_file($path . DIRECTORY_SEPARATOR . $item)) $files[] = $item;
    }
    
    return $files;
  }
  
  public function mkdir($directory, $permission = 0755)
  {
    if (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DIRECTORY_SEPARATOR . $directory;
    }
    
    if ($this->isDir($directory) || $this->isFile($directory)) {
      $message = "cannot create directory '{$directory}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->_mkdir($directory, $permission);
      return new self($directory);
    }
  }
  
  public function mkfile($file, $permission = 0755)
  {
    if (!$this->isAbsolutePath($file)) {
      $file = $this->path . DIRECTORY_SEPARATOR . $file;
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
  
  public function getList()
  {
    $items = array();
    foreach (scandir($this->path) as $item) {
      if ($item === "." || $item === "..") continue;
      $path = $this->path . DIRECTORY_SEPARATOR . $item;
      if (is_file($path)) {
        $items[] = new Sabel_Util_FileSystem_File($path);
      } else {
        $items[] = new self($path);
      }
    }
    
    return $items;
  }
  
  public function rmdir($directory = null)
  {
    if ($directory === null) {
      $directory = $this->path;
    } elseif (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DIRECTORY_SEPARATOR . $directory;
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
  
  public function copy($dest, $src = null)
  {
    if ($src === null) $src = $this->path;
    
    if (!$this->isAbsolutePath($dest)) {
      $dest = dirname($this->path) . DIRECTORY_SEPARATOR . $dest;
    }
    
    $dir = new self($src);
    $this->_mkdir($dest, $dir->getPermission());
    
    if ($items = $dir->getList()) {
      foreach ($items as $item) {
        $destination = $dest . DIRECTORY_SEPARATOR . basename($item->getPath());
        
        if ($item->isFile()) {
          $item->copy($destination, true);
        } else {
          $this->copy($destination, $item->pwd());
        }
      }
    }
  }
}
