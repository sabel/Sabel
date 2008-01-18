<?php

class SabelTestCaseBase extends PHPUnit_Framework_TestCase
{
  protected static function createSuite($name)
  {
    return new PHPUnit_Framework_TestSuite($name);
  }
}

class SabelTestCase extends SabelTestCaseBase
{
  /**
   * override parents runBare()
   *
   * @access public
   */
  public function runBare()
  {
    $catchedException = NULL;
    
    try {
      $this->setUp();
    } catch (Exception $e) {
      echo "setUp throws exception: " . $e->getMessage() . "\n";
    }
    
    try {
      $this->runTest();
    } catch (Exception $e) {
      $catchedException = $e;
    }
    
    try {
      $this->tearDown();
    } catch (Exception $e) {
      echo "tearDown throws exception: " . $e->getMessage() . "\n";
    }
    
    // Workaround for missing "finally".
    if ($catchedException !== NULL) {
      throw $catchedException;
    }
  }
}
