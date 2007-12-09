<?php

require_once ("PHPUnit/TextUI/TestRunner.php");
require_once ("PHPUnit/Framework/TestCase.php");

class Sabel_Test_Runner extends PHPUnit_TextUI_TestRunner
{
  private $classPrefix = "Units_";
  
  public static function create()
  {
    return new self();
  }
    
  public function start($testName, $testFilePath)
  {
    if (is_readable($testFilePath)) {
      try {
        $testCaseName = $this->classPrefix . $testName;
        $testSuite = $this->getTest($testCaseName, $testFilePath);
        
        if ($testSuite instanceof PHPUnit_Framework_TestSuite) {
          $this->doRun($testSuite);
        }
      } catch (Exception $e) {
        Sabel_Cli::error("could not run test suite: " . $e->getMessage());
      }
    } else {
      Sabel_Cli::error($testFilePath . " not found");
    }
  }
  
  public function setClassPrefix($prefix)
  {
    $this->classPrefix = $prefix;
  }
}
