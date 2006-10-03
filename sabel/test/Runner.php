<?php

/**
 * Sabel specific Test_Runner inherit PHPUnit2_TextUI_TestRunner
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Runner extends PHPUnit2_TextUI_TestRunner {
  public static function running($test, $testFile)
  {
    $self = new self();
    $self->start(array($test, $testFile));
  }
  
  public function start($arguments)
  {
    $test     = isset($arguments[0]) ? $arguments[0] : false;
    $testFile = isset($arguments[1]) ? $arguments[1] : $test . '.php';
    
    try {
      $this->doRun($this->getTest($test, $testFile));
    } catch (Exception $e) {
      throw new Exception(
        'Could not create and run test suite:'. $e->getMessage()
      );
    }
  }
}