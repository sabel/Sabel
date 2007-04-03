<?php

require ("PHPUnit/TextUI/TestRunner.php");

class Sabel_Test_ModelRunner extends PHPUnit_TextUI_TestRunner
{
  public static function create()
  {
    return new self();
  }
  
  public function start($arguments)
  {
    $test = (isset($arguments)) ? $arguments : false;
    $testCaseName   = "Units_".ucfirst($arguments);
    $pathToTestCase = RUN_BASE . "/tests/units/" . $test . '.php';
    
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