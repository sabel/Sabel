<?php

/**
 * Sabel_View_Location_File
 *
 * @category   View
 * @package    org.sabel.view.location
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Location_File extends Sabel_View_Location
{
  private $resources = array();
  
  private $path = "";
  
  public function setPath($path)
  {
    $this->path = $path;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function isResourceValid($name)
  {
    return (is_readable($this->path . $name));
  }
  
  public function getResource($name)
  {
    $name .= TPL_SUFFIX;
    
    if (!$this->isResourceValid($name)) return false;
    
    $resource = new Sabel_View_Resource_Template();
    $resource->setPath($this->path);
    $resource->setName($name);
    $resource->valid = ($this->isResourceValid($name));
    
    return $resource;
  }
  
  public function getResourceList()
  {
    $dir = opendir($this->path);
    
    $resourceFiles = array();
    
    if ($dir) {
      while (($filename = readdir($dir)) !== false) {
        if ($filename !== "." && $filename !== "..") {
          $resourceFiles[] = str_replace(TPL_SUFFIX, "", $filename);
        }
      }
    }
    
    return $resourceFiles;
  }
}
