<?php

/**
 * Sabel_View_Template
 *
 * @abstract
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_View_Template extends Sabel_Object
{
  protected $defaultPath = "";
  protected $paths = array();
  
  abstract public function find($tplPath);
  abstract public function create($locationName, $tplPath, $body = "");
  abstract public function delete($locationName, $tplPath);
  abstract public function isValid($locationName, $tplPath);
  
  public function __construct($defaultPath)
  {
    $this->paths[] = array("name" => "default",
                           "path" =>  MODULES_DIR_PATH . DS . $defaultPath);
  }
  
  public function addPath($name, $path)
  {
    $param = array("name" => $name, "path" => MODULES_DIR_PATH . DS . $path);
    array_unshift($this->paths, $param);
  }
  
  public function getPaths()
  {
    return $this->paths;
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
