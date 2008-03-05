<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * UnitTest
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class UnitTest extends Tests
{
  public function run()
  {
    $runner = Sabel_Test_Runner::create();
    $runner->setClassPrefix("Units_");
    
    $testsDir = RUN_BASE . DS . "tests" . DS . "units";
    
    if (count($this->arguments) === 0) {
      foreach (scandir($testsDir) as $file) {
        if (preg_match("/^[A-Z].+" . PHP_SUFFIX . "$/", $file)) {
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
    echo "Usage: sakle UnitTest [TESTCASE_NAME]" . PHP_EOL;
    echo PHP_EOL;
    echo "  TESTCASE_NAME: all run the unit tests, if omit a testcase name" . PHP_EOL;
    echo PHP_EOL;
    echo "Example: sakle UnitTest MyTest" . PHP_EOL;
    echo PHP_EOL;
  }
}
