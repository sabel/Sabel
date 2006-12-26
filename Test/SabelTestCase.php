<?php

class SabelTestCase extends PHPUnit2_Framework_TestCase
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
