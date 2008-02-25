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
class Sabel_Util_FileSystem extends Sabel_Util_FileSystem_Directory
{
  protected $path = "";
  
  public function __construct($base = "")
  {
    if ($base === "") {
      $this->path = (Sabel_Environment::create()->isWin()) ? "C:\\" : "/";
    } else {
      $this->path = $base;
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
      $path = $this->path . DS . $path;
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
      $path = $this->path . DS . $path;
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
      $path = $this->path . DS . $path;
    }
    
    if ($this->isDir($path)) {
      return new Sabel_Util_FileSystem_Directory($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getFile($path)
  {
    if (!$this->isAbsolutePath($path)) {
      $path = $this->path . DS . $path;
    }
    
    if ($this->isFile($path)) {
      return new Sabel_Util_FileSystem_File($path);
    } else {
      $message = "'{$path}': no such file or directory.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
}
