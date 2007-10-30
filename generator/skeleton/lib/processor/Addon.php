<?php

/**
 * Processor_Addon
 *
 * @category Processor
 * @package  lib.processor
 * @version  1.0
 */
class Processor_Addon extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $addons = array();
    $addonDir = RUN_BASE . "/addon/";
    
    $this->getFiles($addonDir, $addons);
    
    foreach ($addons as $addonName) {
      $myAddonDir = $addonDir . $addonName;
      require ($myAddonDir . "/Addon.php");
      $addonInitializeClassName = ucfirst($addonName) . "_Addon";
      if (class_exists($addonInitializeClassName)) {
        $addon = new $addonInitializeClassName();
        $switch = $addon->load();
        
        if ($switch) {
          $addonLibDir = $myAddonDir . "/lib";
          $processorClassFile = $myAddonDir . "/Processor.php";
          if (is_readable($myAddonDir . "/Processor.php")) {
            require ($processorClassFile);
            $addon->loadProcessor($bus);
          }
        }
      } else {
        // exception
      }
    }
  }
  
  private function getFiles($dir, &$files)
  {
    $iterator = new DirectoryIterator($dir);
    foreach ($iterator as $file) {
      $filename = $file->getFilename();
      
      if ($file->isDot() ||
         $filename === ".svn" || $filename === ".cvs") {
         continue;
      }
         
      $files[] = $filename;
    }
  }
}
