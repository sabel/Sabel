<?php

/**
 * Sabel_View_Resource_Template
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Resource_Template implements Sabel_View_Resource_File
{
  private $path     = "";
  private $name     = "";
  private $missing  = false;
  
  public final function setPath($path)
  {
    if (is_string($path)) {
      $this->path = $path;
    } else {
      throw new Exception("path must be string");
    }
  }
  
  public final function getPath()
  {
    return $this->path;
  }
  
  public final function setName($name)
  {
    if (is_string($name)) {
      $this->name = $name;
    } else {
      throw new Exception("name must be string");
    }
  }
  
  public final function getName()
  {
    return $this->name;
  }
  
  public final function setFullPath($path, $name)
  {
    $this->setPath($path);
    $this->setName($name);
  }
  
  public final function getFullpath()
  {
    return $this->path . $this->name;
  }
  
  public function fetch()
  {
    if ($this->isValid()) {
      return file_get_contents($this->path . $this->name);
    } else {
      return false;
    }
  }
    
  public function isValid()
  {
    return (is_readable($this->path.$this->name));
  }
  
  public function missing()
  {
    $this->missing = true;
  }
  
  public function isMissing()
  {
    return $this->missing;
  }
}
