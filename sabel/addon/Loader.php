<?php

/**
 * Addon Loader
 *
 * @category   Addon
 * @package    org.sabel.addon
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Addon_Loader extends Sabel_Object
{
  private
    $dir  = "",
    $name = "",
    $bus  = null;
    
  public function __construct($dir, $name, $bus = null)
  {
    $this->dir  = $dir;
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
    
    if (is_file($pathToAddonClass)) {
      $dirs = explode(DS, dirname($pathToAddonClass));
      $dir = $dirs[count($dirs) - 1];
      
      $includePathDefine = strtoupper($addonName) . "_ADDON_INCLUDE_PATH";
      $pathDefine = strtoupper($addonName) . "_ADDON_PATH";
      
      if (!defined($includePathDefine)) {
        define($includePathDefine, DS . ADDON_DIR_NAME . DS . $dir);
        define($pathDefine, RUN_BASE . constant($includePathDefine));
      }
      
      Sabel::fileUsing($pathToAddonClass, true);
      $addonInitializeClassName = ucfirst($addonName) . "_Addon";
      
      if (class_exists($addonInitializeClassName)) {
        $addon = new $addonInitializeClassName();
        $switch = $addon->load();
      }
    } else {
      throw new Sabel_Exception_FileNotFound($pathToAddonClass);
    }
    
    if ($switch && $this->bus !== null) {
      $processorClassFile = $myAddonDir . DS . "Processor" . PHP_SUFFIX;
      
      if (is_readable($processorClassFile)) {
        Sabel::fileUsing($processorClassFile, true);
        $addon->loadProcessor($this->bus);
      } else {
        throw new Sabel_Exception_FileNotFound($processorClassFile);
      }
    }
  }
}
