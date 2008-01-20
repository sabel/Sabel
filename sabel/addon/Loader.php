<?php

/**
 * Addon Loader
 *
 * @category   Addon
 * @package    org.sabel.addon
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Addon_Loader extends Sabel_Object
{
  private $addonDir = "";
  
  public function __construct($addonDir)
  {
    $this->addonDir = $addonDir;
  }
  
  public function load($addonName, $bus = null)
  {
    $myAddonDir = $this->addonDir . DS . $addonName;
    $pathToAddonClass = $myAddonDir . DS . "Addon" . PHP_SUFFIX;
    
    if (Sabel::fileUsing($pathToAddonClass, true)) {
      $addonInitializeClassName = ucfirst($addonName) . "_Addon";
      $addon = new $addonInitializeClassName();
    } else {
      throw new Sabel_Exception_FileNotFound($pathToAddonClass);
    }
    
    if ($addon->load() && $bus !== null) {
      $processorClassFile = $myAddonDir . DS . "Processor" . PHP_SUFFIX;
      
      if (Sabel::fileUsing($processorClassFile, true)) {
        $addon->loadProcessor($bus);
      }
    }
  }
}
