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
class Sabel_Util_FileSystem extends Sabel_Object
{
  protected $isWin   = false;
  protected $current = "";
  
  public function __construct($base = "")
  {
    $this->isWin = Sabel_Environment::create()->isWin();
    
    if ($base === "") {
      $this->current = ($this->isWin) ? "C:\\" : "/";
    } else {
      $this->current = $base;
    }
  }
  
  public function pwd()
  {
    return $this->current;
  }
  
  public function cd($path)
  {
    clearstatcache();
    
    if (is_dir($this->current . DS . $path)) {
      $this->current = $this->current . DS . $path;
    } else {
      trigger_error("no such file or directory.", E_USER_WARNING);
    }
  }
  
  public function ls($path = null)
  {
    if ($path === null) {
      $path = $this->current;
    } elseif (!$this->isAbsolutePath($path)) {
      $path = $this->current . DS . $path;
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
    if ($this->isDir($path)) {
      if (!$this->isAbsolutePath($path)) {
        $path = $this->current . DS . $path;
      }
      
      return new Sabel_Util_FileSystem_Directory($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getFile($path)
  {
    if ($this->isFile($path)) {
      if (!$this->isAbsolutePath($path)) {
        $path = $this->current . DS . $path;
      }
      
      return new Sabel_Util_FileSystem_File($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function mkdir($directory, $permission = 0755)
  {
    if (!$this->isAbsolutePath($directory)) {
      $directory = $this->current . DS . $directory;
    }
    
    if ($this->isDir($directory) || $this->isFile($directory)) {
      $message = "cannot create directory '{$directory}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->_mkdir($directory, $permission);
      return $this->getDirectory($directory);
    }
  }
  
  public function mkfile($file, $permission = 0755)
  {
    if (!$this->isAbsolutePath($file)) {
      $file = $this->current . DS . $file;
    }
    
    if ($this->isDir($file) || $this->isFile($file)) {
      $message = "cannot create directory '{$file}': "
               . "file or directory exists.";
      
      throw new Sabel_Exception_Runtime($message);
    } else {
      $this->_mkfile($file, $permission);
    }
  }
  
  public function rmdir($directory)
  {
    if (!$this->isAbsolutePath($directory)) {
      $directory = $this->current . DS . $directory;
    }
    
    if (!$this->isDir($directory)) {
      trigger_error("no such file or directory.", E_USER_WARNING);
    } elseif ($this->isFile($directory)) {
      trigger_error("'{$directory}': not a directory.", E_USER_WARNING);
    } else {
      $this->_rmdir($directory);
    }
  }
  
  public function isDir($directory)
  {
    if (!$this->isAbsolutePath($directory)) {
      $directory = $this->current . DS . $directory;
    }
    
    clearstatcache();
    return is_dir($directory);
  }
  
  public function isFile($file)
  {
    if (!$this->isAbsolutePath($file)) {
      $file = $this->current . DS . $file;
    }
    
    clearstatcache();
    return is_file($file);
  }
  
  protected function isAbsolutePath($path)
  {
    if ($this->isWin) {
      return (preg_match("@^[a-zA-Z]:\\\\@", $path) === 1);
    } else {
      return ($path{0} === "/");
    }
  }
  
  protected function _mkdir($directory, $permission)
  {
    clearstatcache();
    
    // @todo for windows
    
    $path  = "";
    $parts = explode(DS, $directory);
    
    foreach ($parts as $part) {
      if ($part === "") continue;
      $path .= DS . $part;
      
      if (!is_dir($path)) {
        mkdir($path);
        chmod($path, $permission);
      }
    }
  }
  
  protected function _mkfile($filePath, $permission)
  {
    clearstatcache();
    
    // @todo for windows
    
    $path  = "";
    $parts = explode(DS, $filePath);
    $file  = array_pop($parts);
    
    foreach ($parts as $part) {
      if ($part === "") continue;
      $path .= DS . $part;
      
      if (!is_dir($path)) {
        mkdir($path);
        chmod($path, $permission);
      }
    }
    
    file_put_contents($filePath, "");
    chmod($filePath, $permission);
  }
  
  protected function _rmdir($directory)
  {
    clearstatcache();
    
    foreach (scandir($directory) as $item) {
      if ($item === "." || $item === "..") continue;
      $path = $directory . DS . $item;
      if (is_file($path)) {
        unlink($path);
      } else {
        if (is_dir($path)) $this->_rmdir($path);
        rmdir($path);
      }
    }
    
    rmdir($directory);
  }
}
