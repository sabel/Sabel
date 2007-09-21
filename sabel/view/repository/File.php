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
  
  private $locations = array();
  
  public function __construct($destination)
  {
    $this->initialize($destination);
  }
  
  public function initialize($destination)
  {
    $locations = array();
    $this->destination = $destination;
    
    list ($module, $controller,) = $destination->toArray();
    
    $base = $this->getPathToBaseDirectory($module);
    
    // app/views/
    $rootLocation = new Sabel_View_Location_File("root", $destination);
    $rootLocation->setPath(RUN_BASE . self::APP_VIEW);
    
    // app/{module}/views/
    $moduleLocation = new Sabel_View_Location_File("module", $destination);
    $moduleLocation->setPath($base . self::VIEW_DIR);
    
    // app/{module}/views/{controller}/
    $leafLocation = new Sabel_View_Location_File("leaf", $destination);
    $leafLocation->setPath($base . self::VIEW_DIR . $controller . DIR_DIVIDER);
    
    $locations[$rootLocation->getName()]   = $rootLocation;
    $locations[$moduleLocation->getName()] = $moduleLocation;
    $locations[$leafLocation->getName()]   = $leafLocation;
    
    $this->locations = $locations;
  }
  
  /**
   * implements Sabel_View_Repository
   */
  public function find($action = null)
  {
    $destination = $this->getDestination($action);
    
    $action = $destination->getAction();
    $templateName = $action;
    
    foreach ($this->locations as $location) {
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
    return $this->locations[$locationName]->getResource($name);
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
  
  protected function getPathToResource($locationName, $action)
  {
    $destination = $this->getDestination($action);
    list ($module, $controller, $action) = $destination->toArray();
    
    $action = $destination->getAction();
    $templateName = $action . TPL_SUFFIX;
    
    if (!isset($this->locations[$locationName])) {
      throw new Sabel_Exception_Runtime("no location");
    }
    
    return $this->locations[$locationName]->getPath() . $templateName;
  }
  
  public function getResourceList($locationName)
  {
    return $this->locations[$locationName]->getResourceList();
  }
  
  public function isResourceValid($locationName, $name)
  {
    return $this->locations[$locationName]->isResourceValid($name);
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
  
  protected function getPathToBaseDirectory($module)
  {
    return RUN_BASE . DS . self::MODULES_DIR . DS . $module . DIR_DIVIDER;
  }
}
