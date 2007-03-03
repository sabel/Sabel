<?php

if (!defined('PHPUnit_MAIN_METHOD'))
  define('PHPUnit_MAIN_METHOD', 'Tester::main');

require_once('PHPUnit/TextUI/TestRunner.php');
require_once('PHPUnit/Framework/TestCase.php');

/**
 * functional test for Sabel Application
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
    $fixtureName = "Fixtures_" . array_pop(explode("_", $ref->getName()));
    //Sabel::using($fixtureName);
    
    try {
      if (class_exists($fixtureName)) eval("{$fixtureName}::upFixture();");
      $this->setUp();
    } catch (Exception $e) {
      echo "fixture throws exception: " . $e->getMessage() . "\n";
    }
    
    try {
      $this->runTest();
    } catch (Exception $e) {
      $catchedException = $e;
    }
    
    try {
      if (class_exists($fixtureName)) eval("{$fixtureName}::downFixture();");
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