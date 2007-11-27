<?php

define("UNIT_TEST", true);

require_once("PHPUnit/TextUI/TestRunner.php");
require_once("PHPUnit/Framework/TestCase.php");

class Sabel_Test_ModelRunner extends PHPUnit_TextUI_TestRunner
{
  public static function create()
  {
    return new self();
  }
  
  public function start($testName)
  {
    $pathToTestCase = $this->getTestsDirectory() . DS . $testName . PHP_SUFFIX;
    
    if (is_readable($pathToTestCase)) {
      try {
        $testCaseName = "Units_" . $testName;
        $this->doRun($this->getTest($testCaseName, $pathToTestCase));
      } catch (Exception $e) {
        throw new Exception("Could not run test suite: " . $e->getMessage());
      }
    } else {
      throw new Exception($pathToTestCase . " not found");
    }
  }
  
  public function getTestsDirectory()
  {
    return RUN_BASE . DS . "tests" . DS . "units";
  }
}
