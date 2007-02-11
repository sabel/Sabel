<?php

define("RUN_BASE", getcwd());
add_include_path("/tests");

if (!defined('PRODUCTION'))  define('PRODUCTION',  0x01);
if (!defined('TEST'))        define('TEST',        0x05);
if (!defined('DEVELOPMENT')) define('DEVELOPMENT', 0x0A);

add_include_path('/app');
add_include_path('/app/models');
add_include_path('/lib');

define("__TRUE__",  "true");
define("__FALSE__", "false");

Sabel::fileUsing("config/database.php");

Sabel::using('Sabel_Sakle_Task');

Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_DB_Model');

Sabel::using("Sabel_Test_Functional");
Sabel::using("Sabel_Test_FunctionalRunner");

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