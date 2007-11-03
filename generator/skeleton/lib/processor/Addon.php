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
    $addonDir = ADDON_DIR_PATH . DS;
    
    $this->getFiles($addonDir, $addons);
    
    foreach ($addons as $addonName) {
      $myAddonDir = $addonDir . $addonName;
      $addonInitializeClassName = ucfirst($addonName) . "_Addon";
      if (class_exists($addonInitializeClassName)) {
        $addon = new $addonInitializeClassName();
        
        if ($addon->load()) {
          l("[addon] load addon " . $addonName);
          $processorClassFile = $myAddonDir . DS . "Processor.php";
          if (is_readable($processorClassFile)) {
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
