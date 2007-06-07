<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks/environment.php");
Sabel::fileUsing("config/connection.php");

/**
 * Functional
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class TestSuite extends Sabel_Sakle_Task
{
  public function run($arguments)
  {
    if (isset($arguments[2])) {
      define ("ENVIRONMENT", environment($arguments[2]));
    } else {
      define ("ENVIRONMENT", TEST);
    }
    
    $pathToTest = RUN_BASE . '/tests/functional';
    $dir = new DirectoryIterator($pathToTest);

    $tests = array();
    foreach ($dir as $element) {
      if ($element->isFile() && strpos($element->getFileName(), '.') !== 0) {
        require ($pathToTest . "/" . $element->getFileName());
        $tests[] = $element->getFileName(). "\n";
      }
    }
    
    foreach ($tests as $test) {
      $name = explode(".", $test);
      $this->printMessage("RUN: " . $name[0]);
      Sabel_Test_FunctionalRunner::create()->start($name[0]);
      echo "\n";
    }
  }
}