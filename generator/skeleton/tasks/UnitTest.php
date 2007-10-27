<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks" . DS . "environment.php", true);
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
    $this->initialize($arguments);
    
    $mRunner = Sabel_Test_ModelRunner::create();
    if (count($this->arguments) === 1) {
      foreach (scandir($mRunner->getTestsDirectory()) as $file) {
        if (preg_match("/^[A-Z].+" . PHP_SUFFIX . "/", $file)) {
          $mRunner->start(str_replace(PHP_SUFFIX, "", $file));
          echo "Complete: {$file}\n";
        }
      }
    } else {
      $mRunner->start($arguments[1]);
      echo "Complete: {$arguments[1]}\n";
    }
  }
}
