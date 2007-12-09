<?php

class Sabel_Addon_Loader extends Sabel_Object
{
  private $dir = "";
  private $name = "";
  private $bus = null;
  
  public function __construct($dir, $name, $bus = null)
  {
    $this->dir = $dir;
    $this->name = $name;
    
    if ($bus !== null) {
      $this->bus = $bus;
    }
  }
  
  public function load($dir = null, $name = null)
  {
    $addonDir   = ($dir  !== null) ? $dir  : $this->dir;
    $addonName  = ($name !== null) ? $name : $this->name;
    $myAddonDir = $addonDir . DS . $addonName;
    $pathToAddonClass = $myAddonDir . DS . "Addon" . PHP_SUFFIX;
    
    if (is_readable($pathToAddonClass)) {
      $dirs = explode(DS, dirname($pathToAddonClass));
      $dir = $dirs[count($dirs) - 1];
      
      $includePathDefine = strtoupper($addonName) . "_ADDON_INCLUDE_PATH";
      $pathDefine = strtoupper($addonName) . "_ADDON_PATH";
      define($includePathDefine, DS . ADDON_DIR_NAME . DS . $dir);
      define($pathDefine, RUN_BASE . constant($includePathDefine));
      
      require ($pathToAddonClass);
      $addonInitializeClassName = ucfirst($addonName) . "_Addon";
      
      if (class_exists($addonInitializeClassName)) {
        $addon = new $addonInitializeClassName();
        $switch = $addon->load();
      }
    } else {
      throw new Sabel_Exception_Runtime($pathToAddonClass . " not readable");
    }
    
    if ($switch && $this->bus !== null) {
      $processorClassFile = $myAddonDir . DS . "Processor" . PHP_SUFFIX;
      if (is_readable($processorClassFile)) {
        require ($processorClassFile);
        $addon->loadProcessor($this->bus);
      }
    }
  }
}
