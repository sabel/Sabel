<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("config" . DS . "INIT.php", true);
Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * UnitTest
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class AddonTest extends Tests
{  
  public function run($arguments)
  {
    $addonName = strtolower($arguments[1]);
    $testName = $arguments[2];
    $base = ADDON_DIR_PATH . DS . $addonName . DS . "tests";
    
    $loader = new Sabel_Addon_Loader(ADDON_DIR_PATH.DS, $addonName);
    $loader->load();
    
    $mRunner = Sabel_Test_ModelRunner::create($base);
    $mRunner->setClassPrefix("Css_Tests_");
    
    $mRunner->start($testName, $base);
    echo "Complete: {$arguments[1]}\n";
  }
}
