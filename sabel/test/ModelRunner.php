<?php

require ("PHPUnit/TextUI/TestRunner.php");

class Sabel_Test_ModelRunner extends PHPUnit_TextUI_TestRunner
{
  private $classPrefix = "Units_";
  
  public static function create()
  {
    return new self();
  }
    
  public function start($testName, $base)
  {
    $pathToTestCase = $base . DS . $testName . PHP_SUFFIX;
    
    if (is_readable($pathToTestCase)) {
      try {
        $testCaseName = $this->classPrefix . $testName;
        $testSuite = $this->getTest($testCaseName, $pathToTestCase);
        
        if ($testSuite instanceof PHPUnit_Framework_TestSuite) {
          $this->doRun($testSuite);
        }
      } catch (Exception $e) {
        throw new Exception("Could not run test suite: " . $e->getMessage());
      }
    } else {
      throw new Exception($pathToTestCase . " not found");
    }
  }
  
  public function setClassPrefix($prefix)
  {
    $this->classPrefix = $prefix;
  }
}
