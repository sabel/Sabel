<?php

/**
 * Processor_Helper
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Helper_Processor extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $moduleName = $this->destination->getModule();
    $controllerName = $this->destination->getController();
    
    $sharedHelper  = "application";
    $commonHelpers = MODULES_DIR_PATH . DS . HELPERS_DIR_NAME;
    $moduleHelpers = MODULES_DIR_PATH . DS . $moduleName . DS . HELPERS_DIR_NAME;
    
    $helpers = array();
    
    $helpers[] = $commonHelpers . DS . $sharedHelper;
    $helpers[] = $moduleHelpers . DS . $sharedHelper;
    $helpers[] = $moduleHelpers . DS . $controllerName;
    
    foreach ($helpers as $helper) {
      Sabel::fileUsing($helper . PHP_SUFFIX, true);
    }
  }
}
