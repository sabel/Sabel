<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * UnitTest
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@gmail.com>
 *             Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class UnitTest extends Tests
{  
  public function run($arguments)
  {
    $mRunner = Sabel_Test_ModelRunner::create();
    
    if (count($this->arguments) === 1) {
      foreach (scandir($mRunner->getTestsDirectory()) as $file) {
        if (preg_match("/^[A-Z].+" . PHP_SUFFIX . "/", $file)) {
          $mRunner->start(str_replace(PHP_SUFFIX, "", $file));
          $this->success("Complete: $file");
        }
      }
    } else {
      $mRunner->start($arguments[1]);
      $this->success("Complete: {$arguments[1]}");
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle UnitTest";
  }
}
