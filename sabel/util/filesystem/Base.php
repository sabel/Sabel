<?php

/**
 * Sabel_Util_FileSystem_Base
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Util_FileSystem_Base extends Sabel_Object
{
  public function isDir($directory)
  {
    if (!$this->isAbsolutePath($directory)) {
      $directory = $this->path . DS . $directory;
    }
    
    clearstatcache();
    return is_dir($directory);
  }
  
  public function isFile($file)
  {
    if (!$this->isAbsolutePath($file)) {
      $file = $this->path . DS . $file;
    }
    
    clearstatcache();
    return is_file($file);
  }
  
  protected function isAbsolutePath($path)
  {
    static $isWin = null;
    
    if ($isWin === null) {
      $isWin = Sabel_Environment::create()->isWin();
    }
    
    if ($isWin) {
      return (preg_match("@^[a-zA-Z]:\\\\@", $path) === 1);
    } else {
      return ($path{0} === "/");
    }
  }
  
  protected function _mkdir($directory, $permission)
  {
    clearstatcache();
    
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
    
    $this->_mkdir(dirname($filePath), $permission);
    
    $fileName = basename($filePath);
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
      } elseif (is_dir($path)) {
        $this->_rmdir($path);
        rmdir($path);
      }
    }
  }
}
