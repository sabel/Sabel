<?php

/**
 * Sabel_View_Locator
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Locator_File implements Sabel_View_Locator
{
  const VIEW_DIR   = "views/";
  const APP_VIEW   = "/app/views/";
  const DEF_LAYOUT = "layout.tpl";
  const TPL_SUFFIX = ".tpl";
  
  private $locations = array();
  
  /**
   * locate resource from file
   * 
   * @param Sabel_Destination $destination
   * @return Sabel_View_Resource
   */
  public function locate($destination)
  {
    list($module, $controller, $name) = $destination->toArray();
    
    $location = $this->getLocation($name);
    $resource = new Sabel_View_Resource_Template();
    
    if ($location->valid) {
      $resource->setRenderer($location->renderer);
      $resource->setPath($location->path);
      $resource->setName($location->name);
    } else {
      $resource->missing();
    }
    
    return $resource;
  }
  
  private final function getLocation()
  {
    $location = new StdClass();
    foreach ($this->locations as $l) {
      list($path, $name) = $l;
      if (is_readable($path . $name)) {
        $location->path = $path;
        $location->name = $name;
        $location->renderer = new Sabel_View_Renderer_Class();
        $location->valid = true;
        return $location;
      }
    }
    
    $location->valid = false;
    return $location;
  }
  
  public final function addLocation($path, $name)
  {
    $this->locations[] = array($path, $name);
  }
}
