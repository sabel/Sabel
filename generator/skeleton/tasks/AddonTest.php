<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * AddonTest
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class AddonTest extends Tests
{
  public function run($arguments)
  {
    if (count($this->arguments) === 1) {
      $this->usage();
      exit;
    }
    
    $addonName = strtolower($this->arguments[1]);
    $runner = Sabel_Test_Runner::create();
    $runner->setClassPrefix($addonName . "_Tests_");
    
    $testsDir = ADDON_DIR_PATH . DS . $addonName . DS . "tests";
    $loader = new Sabel_Addon_Loader(ADDON_DIR_PATH);
    $loader->load($addonName);
    
    if (count($this->arguments) === 2) {
      foreach (scandir($testsDir) as $file) {
        if (preg_match("/^[A-Z].+" . PHP_SUFFIX . "/", $file)) {
          $testName = str_replace(PHP_SUFFIX, "", $file);
          $runner->start($testName, $testsDir . DS . $file);
          $this->success("Complete: $testName");
        }
      }
    } else {
      $testName = $this->arguments[1];
      $runner->start($testName, $testsDir . DS . $testName. PHP_SUFFIX);
      $this->success("Complete: $testName");
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle AddonTest ADDON_NAME TEST_NAME" . PHP_EOL;
  }
}
