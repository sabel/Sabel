<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * UnitTest
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class UnitTest extends Tests
{  
  public function run($arguments)
  {
    $base = TEST_DIR_PATH . DS . "units";
    $mRunner = Sabel_Test_ModelRunner::create($base);
    
    if (count($this->arguments) === 1) {
      foreach (scandir($base) as $file) {
        if (preg_match("/^[A-Z].+" . PHP_SUFFIX . "/", $file)) {
          $mRunner->start(str_replace(PHP_SUFFIX, "", $file), $base);
          $this->success("Complete: $file");
        }
      }
    } else {
      $mRunner->start($arguments[1], $base);
      $this->success("Complete: {$arguments[1]}");
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle UnitTest";
  }
}
