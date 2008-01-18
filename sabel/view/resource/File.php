<?php

/**
 * Sabel_View_Resource_File
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Resource_File
  extends Sabel_Object implements Sabel_View_Resource_Interface
{
  private $path = "";
  private $locationName = "";
  
  public function __construct($locationName, $path)
  {
    $this->path = $path;
    $this->locationName = $locationName;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function getLocationName()
  {
    return $this->locationName;
  }
  
  public function fetch()
  {
    if ($this->isValid()) {
      return file_get_contents($this->path);
    } else {
      return false;
    }
  }
  
  public function isValid()
  {
    return (is_file($this->path) && is_readable($this->path));
  }
  
  public function create($contents)
  {
    file_put_contents($this->path, $contents);
  }
  
  public function delete()
  {
    unlink($this->path);
  }
}
