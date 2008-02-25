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
class Sabel_Util_FileSystem_File extends Sabel_Object
{
  protected $path = "";
  protected $contents = array();
  
  public function __construct($path)
  {
    $this->path = $path;
  }
  
  public function getPath()
  {
    return $this->path;
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
  
  public function clear()
  {
    file_put_contents($this->path, "");
  }
  
  public function remove()
  {
    unlink($this->path);
  }
}
