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
      $loader = new Sabel_Addon_Loader($addonDir, $addonName, $bus);
      $loader->load($addonDir, $addonName);
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
