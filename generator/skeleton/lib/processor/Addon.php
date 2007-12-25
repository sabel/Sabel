<?php

/**
 * Processor_Addon
 *
 * @category   Processor
 * @package    lib.processor
 * @version    1.0
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Addon extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $addonDir = ADDON_DIR_PATH;
    $files = $this->getFiles($addonDir);
    
    foreach ($this->getFiles($addonDir) as $addonName) {
      $loader = new Sabel_Addon_Loader($addonDir, $addonName, $bus);
      $loader->load();
    }
  }
  
  private function getFiles($dir)
  {
    $files = array();
    $iterator = new DirectoryIterator($dir);
    
    foreach ($iterator as $file) {
      $filename = $file->getFilename();
      if ($filename{0} === ".") continue;
      $files[] = $filename;
    }
    
    return $files;
  }
}
