<?php

/**
 * Sabel_Util_FileSystem_File
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_FileSystem_File extends Sabel_Util_FileSystem_Base
{
  protected $contents = array();
  
  public function __construct($path)
  {
    $this->path = realpath($path);
  }
  
  public function getFileName()
  {
    return basename($this->path);
  }
  
  public function getSize()
  {
    clearstatcache();
    return filesize($this->path);
  }
  
  public function isReadable()
  {
    clearstatcache();
    return is_readable($this->path);
  }
  
  public function isWritable()
  {
    clearstatcache();
    return is_writable($this->path);
  }
  
  public function isExecutable()
  {
    clearstatcache();
    return is_executable($this->path);
  }
  
  public function atime()
  {
    clearstatcache();
    return fileatime($this->path);
  }
  
  public function mtime()
  {
    clearstatcache();
    return filemtime($this->path);
  }
  
  public function getContents()
  {
    return file_get_contents($this->path);
  }
  
  public function getContentsAsArray()
  {
    $lines = file($this->path);
    array_walk($lines, create_function('&$v', '$v = rtrim($v, PHP_EOL);'));
    
    return $lines;
  }
  
  public function clearContents()
  {
    file_put_contents($this->path, "");
  }
  
  public function open()
  {
    return $this->contents = $this->getContentsAsArray();
  }
  
  public function write($content)
  {
    $this->contents[] = $content;
    
    return $this;
  }
  
  public function save()
  {
    if ($this->contents) {
      $contents = implode(PHP_EOL, $this->contents);
      file_put_contents($this->path, $contents);
    }
  }
  
  public function remove()
  {
    unlink($this->path);
  }
  
  public function copy($dest, $changeName = false)
  {
    if (!$this->isAbsolutePath($dest)) {
      $dest = dirname($this->path) . DS . $dest;
    }
    
    if (!$changeName) $dest .= DS . basename($this->path);
    
    $permission = $this->getPermission();
    $this->_mkdir(dirname($dest), $permission);
    file_put_contents($dest, $this->getContents());
    chmod($dest, $permission);
    
    return new self($dest);
  }
  
  public function move($dest, $changeName = false)
  {
    if (!$this->isAbsolutePath($dest)) {
      $dest = dirname($this->path) . DS . $dest;
    }
    
    if (!$changeName) $dest .= DS . basename($this->path);
    
    $this->copy($dest, true);
    $this->remove();
    $this->path = $dest;
    
    return new self($dest);
  }
}
