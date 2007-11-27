<?php

Sabel::fileUsing("tasks" . DS . "Tests.php", true);

/**
 * Functional
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Functional extends Tests
{
  public function run($arguments)
  {
    $fRunner = Sabel_Test_FunctionalRunner::create();
    
    if (count($this->arguments) === 1) {
      foreach (scandir($fRunner->getTestsDirectory()) as $file) {
        if (preg_match("/^[A-Z].+" . PHP_SUFFIX . "/", $file)) {
          $fRunner->start(str_replace(PHP_SUFFIX, "", $file));
          echo "Complete: {$file}\n";
        }
      }
    } else {
      $fRunner->start($arguments[1]);
      echo "Complete: {$arguments[1]}\n";
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle Functional";
  }
}
