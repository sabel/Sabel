<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * AddonTest
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class AddonTest extends Tests
{
  public function run()
  {
    if (count($this->arguments) === 0) {
      $this->usage();
      exit;
    }
    
    $addonName = strtolower($this->arguments[0]);
    $runner = Sabel_Test_Runner::create();
    $runner->setClassPrefix($addonName . "_Tests_");
    
    $testsDir = RUN_BASE . DS . ADDON_DIR_NAME. DS . $addonName . DS . "tests";
    
    if (count($this->arguments) === 1) {
      foreach (scandir($testsDir) as $file) {
        if (preg_match("/^[A-Z].+\.php$/", $file)) {
          $testName = str_replace(".php", "", $file);
          $runner->start($testName, $testsDir . DS . $file);
        }
      }
    } else {
      $testName = $this->arguments[1];
      $runner->start($testName, $testsDir . DS . $testName . ".php");
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle AddonTest ADDON_NAME [TEST_NAME]" . PHP_EOL;
  }
}
