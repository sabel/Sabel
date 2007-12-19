<?php

if (!defined('PHPUnit_MAIN_METHOD'))
  define('PHPUnit_MAIN_METHOD', 'Tester::main');

require_once('PHPUnit/TextUI/TestRunner.php');
require_once('PHPUnit/Framework/TestCase.php');

/**
 * unit test for Sabel Application
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Model extends PHPUnit_Framework_TestCase
{
  /**
   * override parents runBare()
   *
   * @access public
   */
  public function runBare()
  {
    $catchedException = NULL;
    
    $ref = new ReflectionClass($this);
    $exp = explode("_", $ref->getName());
    $fixtureName = "Fixtures_" . $exp[0];
    
    try {
      if (class_exists($fixtureName)) {
        call_user_func(array($fixtureName, "upFixture"));
      }
      $this->setUp();
    } catch (Exception $e) {
      echo "fixture throws exception: " . $e->getMessage() . "\n";
      call_user_func(array($fixtureName, "downFixture"));
      call_user_func(array($fixtureName, "upFixture"));
    }
    
    try {
      $this->runTest();
    } catch (Exception $e) {
      $catchedException = $e;
    }
    
    try {
      if (class_exists($fixtureName)) {
        call_user_func(array($fixtureName, "downFixture"));
      }
        
      $this->tearDown();
    } catch (Exception $e) {
      $e->getMessage() . "\n";
    }
    
    // Workaround for missing "finally".
    if ($catchedException !== NULL) {
      throw $catchedException;
    }
  }
}
