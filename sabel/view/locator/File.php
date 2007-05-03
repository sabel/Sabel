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
  
  public function locate($name = null)
  {
    if ($name === null) {
      list($module, $controller, $name) = $this->getContext();
    }
    
    $location = $this->getLocation($name);
    
    if (!$location->valid) {
      throw new Exception("resource missing");
    }
    
    $resource = new Sabel_View_Resource_Template();
    $resource->setRenderer($location->renderer);
    $resource->setPath($location->path);
    $resource->setName($location->name);
    
    return $resource;
  }
  
  /*
  private final function getLocation($name)
  {
    $tpldir = Sabel_Const::TEMPLATE_DIR;
    
    list($module, $controller, $action) = $this->getContext();
    $path = $this->getPath($module);
    
    $specificPath  = $path . self::VIEW_DIR;
    $specificName  = $controller . "." . $name . self::TPL_SUFFIX;
    $specificName2 = $controller . "/" . $name . self::TPL_SUFFIX;
    $specificName3 = $controller . "/" . $name;
    
    $location = new StdClass();
    $location->renderer = new Sabel_View_Renderer_Class();
    
    if (is_readable(RUN_BASE . self::APP_VIEW . $name)) {
      $location->path = RUN_BASE . self::APP_VIEW;
      $location->name = $name;
    } elseif (is_readable($specificPath . $specificName2)) {
      $location->path = $specificPath;
      $location->name = $specificName2;
    } elseif (is_readable($specificPath . $specificName)) {
      $location->path = $specificPath;
      $location->name = $specificName;
    } elseif (is_readable($specificPath . $specificName3)) {
      $location->path = $specificPath;
      $location->name = $specificName3;
    } elseif (is_readable($specificPath . $name)) {
      $location->path = $specificPath;
      $location->name = $name;
    } elseif (is_readable($path . $tpldir . $name)) {
      $location->path = $path . $tpldir;
      $location->name = $name;
    } elseif (is_readable($path . $name)) {
      $location->path = $path;
      $location->name = $name;
    } elseif (is_readable($path . $name . self::TPL_SUFFIX)) {
      $location->path = $path;
      $location->name = $name . self::TPL_SUFFIX;
    } else {
      $location->valid = false;
      return $location;
    }
    
    $location->valid = true;
    return $location;
  }*/
  
  private final function getLocation()
  {
    $location = new StdClass();
    foreach ($this->locations as $l) {
      list($path, $name) = $l;
      if (is_readable($path.$name)) {
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
  
  /*
  private final function getPath($module)
  {
    return RUN_BASE . Sabel_Const::MODULES_DIR . $module . DIR_DIVIDER;
  }
  */
  
  private final function getContext()
  {
    $candidate  = Sabel_Context::getCurrentCandidate();
    
    $module     = $candidate->getModule();
    $controller = $candidate->getController();
    $action     = $candidate->getAction();
    
    return array($module, $controller, $action);
  }
}
