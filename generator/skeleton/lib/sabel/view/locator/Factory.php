<?php

class Sabel_View_Locator_Factory
{
  const VIEW_DIR   = "views/";
  const APP_VIEW   = "/app/views/";
  const DEF_LAYOUT = "layout.tpl";
  const TPL_SUFFIX = ".tpl";
  
  public static function create()
  {
    return new self();
  }
  
  public function make($name)
  {
    $locator = new Sabel_View_Locator_File();
    $this->setLocations($locator, $name);
    return $locator;
  }
  
  private final function setLocations($locator, $name)
  {
    if ($name === null) {
      list($module, $controller, $name) = $this->getContext();
    }
    
    $tpldir = Sabel_Const::TEMPLATE_DIR;
    
    list($module, $controller, $action) = $this->getContext();
    $path = $this->getPath($module);
    
    $spcPath = $path . self::VIEW_DIR;
    
    $locator->addLocation(RUN_BASE.self::APP_VIEW, $name);    
    $locator->addLocation($spcPath, $controller."/".$name);
    $locator->addLocation($spcPath, $controller.".".$name.self::TPL_SUFFIX);
    $locator->addLocation($spcPath, $controller."/".$name.self::TPL_SUFFIX);
    $locator->addLocation($path.$tpldir, $name);
    $locator->addLocation($path, $name);
    $locator->addLocation($path, $name.self::TPL_SUFFIX);
  }
  
  private final function getContext()
  {
    $candidate  = Sabel_Context::getCurrentCandidate();
    
    $module     = $candidate->getModule();
    $controller = $candidate->getController();
    $action     = $candidate->getAction();
    
    return array($module, $controller, $action);
  }
  
  private final function getPath($module)
  {
    return RUN_BASE . Sabel_Const::MODULES_DIR . $module . DIR_DIVIDER;
  }
}