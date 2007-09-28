<?php

/**
 * Sabel_View_Repository_File
 *
 * @category   View
 * @package    org.sabel.view.repository
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Hamanaka Kazuhiro <hamanaka.kazuhiro@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Repository_File implements Sabel_View_Repository
{
  const VIEW_DIR    = "views/";
  const APP_VIEW    = "/app/views/";
  const DEF_LAYOUT  = "layout.tpl";
  const MODULES_DIR = "app";
  
  private $destination = null;
  
  private static $locations = array();
  
  public function __construct($destination)
  {
    $this->destination = $destination;
  }
  
  public function addLocation(Sabel_View_Location $viewLocation)
  {
    self::$locations[$viewLocation->getName()] = $viewLocation;
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function find($action = null)
  {
    $destination = $this->getDestination($action);
    
    $action = $destination->getAction();
    $templateName = $action;
    
    foreach (self::$locations as $location) {
      $resource = $location->getResource($templateName);
      if ($resource && !$resource->isMissing()) {
        return $resource;
      }
    }
    
    return false;
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function getResourceFromLocation($locationName, $name)
  {
    return self::$locations[$locationName]->getResource($name);
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function createResource($locationName, $body, $action = null)
  {
    $fp = fopen($this->getPathToResource($locationName, $action), "w+");
    fwrite($fp, $body);
    fclose($fp);
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function editResource($locationName, $body, $action = null)
  {
    $fp = fopen($this->getPathToResource($locationName, $action), "w+");
    fwrite($fp, $body);
    fclose($fp);
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function deleteResource($locationName, $action = null)
  {
    unlink($this->getPathToResource($locationName, $action));
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function getResourceList($locationName)
  {
    return self::$locations[$locationName]->getResourceList();
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function isResourceValid($locationName, $name)
  {
    return self::$locations[$locationName]->isResourceValid($name);
  }
  
  protected function getPathToResource($locationName, $action)
  {
    $destination = $this->getDestination($action);
    list ($module, $controller, $action) = $destination->toArray();
    
    $action = $destination->getAction();
    $templateName = $action . TPL_SUFFIX;
    
    if (!isset(self::$locations[$locationName])) {
      throw new Sabel_Exception_Runtime("no location");
    }
    
    return self::$locations[$locationName]->getPath() . $templateName;
  }
  
  protected function getDestination($action)
  {
    if ($action === null) {
      $destination = $this->destination;
    } else {
      $destination = clone $this->destination;
      $destination->setAction($action);
    }
    
    return $destination;
  }
  
  public function getPathToBaseDirectory($module)
  {
    return RUN_BASE . DS . self::MODULES_DIR . DS . $module . DS;
  }
}
