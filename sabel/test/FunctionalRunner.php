<?php

define("FUNCTIONAL_TEST", true);

require_once('PHPUnit/TextUI/TestRunner.php');
require_once('PHPUnit/Framework/TestCase.php');

class Sabel_Test_FunctionalRunner extends PHPUnit_TextUI_TestRunner
{
  public static function create()
  {
    return new self();
  }
  
  public function start($arguments)
  {
    $test = (isset($arguments)) ? $arguments : false;
    $testCaseName   = "Functional_".ucfirst($arguments);
    $pathToTestCase = RUN_BASE . "/tests/functional/" . $test . '.php';
    
    if (!is_readable($pathToTestCase)) {
      throw new Exception($pathToTestCase . " not found");
    }
    
    try {
      $this->doRun($this->getTest($testCaseName, $pathToTestCase));
    } catch (Exception $e) {
      throw new Exception('Could not run test suite:'. $e->getMessage());
    }
  }
}