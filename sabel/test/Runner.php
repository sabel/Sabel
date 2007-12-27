<?php

require_once ("PHPUnit/TextUI/TestRunner.php");
require_once ("PHPUnit/Framework/TestCase.php");

/**
 * Test Runner
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
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
        Sabel_Command::error("could not run test suite: " . $e->getMessage());
      }
    } else {
      Sabel_Command::error($testFilePath . " not found");
    }
  }
  
  public function setClassPrefix($prefix)
  {
    $this->classPrefix = $prefix;
  }
}
