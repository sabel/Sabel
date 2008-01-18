<?php

/**
 * Sabel_View_Template_File
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Template_File extends Sabel_View_Template
{
  /**
   * @param string $tplPath path from locations
   *
   * @return Sabel_View_Resource or false
   */
  public function find($tplPath)
  {
    foreach ($this->getPaths() as $p) {
      $filePath = $p["path"] . $tplPath . TPL_SUFFIX;
      $resourse = new Sabel_View_Resource_File($p["name"], $filePath);
      if ($resourse->isValid()) return $resourse;
    }
    
    return false;
  }
  
  public function create($locationName, $tplPath, $contents = "")
  {
    $this->getResource($locationName, $tplPath)->create($contents);
  }
  
  public function delete($locationName, $tplPath)
  {
    $this->getResource($locationName, $tplPath)->delete();
  }
  
  public function isValid($locationName, $tplPath)
  {
    return $this->getResource($locationName, $tplPath)->isValid();
  }
  
  public function getResource($locationName, $tplPath)
  {
    foreach ($this->getPaths() as $p) {
      if ($p["name"] === $locationName) {
        $filePath = $p["path"] . $tplPath . TPL_SUFFIX;
        return new Sabel_View_Resource_Template($locationName, $filePath);
      }
    }
    
    $message = "such a location name is not registered.";
    throw new Sabel_Exception_Runtime($message);
  }
}
