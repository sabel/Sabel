<?php

class Sabel_View_Repository_File extends Sabel_Object
{
  const VIEW_DIR    = "views/";
  const APP_VIEW    = "/app/views/";
  const DEF_LAYOUT  = "layout.tpl";
  const MODULES_DIR = "app";
  
  private $locator = null;
  private $locations = array();
  private $create = "";
  private $path = "";
  
  public function __construct()
  {
  }
  
  public function get($module, $controller, $action)
  {
    $destination = new Sabel_Destination($module, $controller, $action);
    $locator = new Sabel_View_Locator_File();
    
    $name = $action;
    
    $locations = array();
    
    $path = $this->getPathToBaseDirectory($module);
    $this->path = $path;
    $spcPath = $path . self::VIEW_DIR;
    $tplFile = $name . TPL_SUFFIX;
    
    // app/views/{action}.tpl
    $locations[] = array("path" => RUN_BASE . self::APP_VIEW, "file" => $tplFile);
    
    // app/{module}/views/{action}.tpl
    $locations[] = array("path" => $spcPath, "file" => $tplFile);
    
    // app/{module}/views/{controller}/{action}.tpl
    $mcaPath = $spcPath . $controller . DIR_DIVIDER;
    $locations[] = array("path" => $mcaPath, "file" => $tplFile);
    
    // app/{module}/views/{controller}.{action}.tpl
    $locations[] = array("path" => $spcPath, "file" => $controller . "." . $tplFile);
        
    foreach ($locations as $l) {
      $locator->addLocation($l["path"], $l["file"]);
    }
    
    $this->locations = $locations;
    
    $this->locator = $locator;
    return $locator->locate($destination);
  }
  
  public function getPathToBaseDirectory($module)
  {
    return RUN_BASE . DS . self::MODULES_DIR . DS . $module . DIR_DIVIDER;
  }
  
  public function createResource($module, $controller, $action, $body)
  {
    $path  = $this->getPathToBaseDirectory($module) . self::VIEW_DIR;
    $path .= $controller . DIR_DIVIDER . $action . TPL_SUFFIX;
    $fp = fopen ($path, "w+");
    fwrite($fp, $body);
    fclose($fp);
  }
  
  public function add($module, $controller, $action)
  {
  }
}