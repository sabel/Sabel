<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * Functional
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@gmail.com>
 *             Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Functional extends Tests
{
  public function run($arguments)
  {
    $runner = Sabel_Test_Runner::create();
    $runner->setClassPrefix("Functional_");
    
    $testsDir = RUN_BASE . DS . "tests" . DS . "functional";
    
    if (count($this->arguments) === 1) {
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
      $this->success("Complete: {$testName}");
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle Functional";
  }
}
