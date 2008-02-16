<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * Functional
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Functional extends Tests
{
  public function run()
  {
    $runner = Sabel_Test_Runner::create();
    $runner->setClassPrefix("Functional_");
    
    $testsDir = RUN_BASE . DS . "tests" . DS . "functional";
    
    if (count($this->arguments) === 0) {
      foreach (scandir($testsDir) as $file) {
        if (preg_match("/^[A-Z].+" . PHP_SUFFIX . "/", $file)) {
          $testName = str_replace(PHP_SUFFIX, "", $file);
          $runner->start($testName, $testsDir . DS . $file);
        }
      }
    } else {
      $testName = $this->arguments[0];
      $runner->start($testName, $testsDir . DS . $testName. PHP_SUFFIX);
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle Functional TESTCASE_NAME" . PHP_EOL;
  }
}
