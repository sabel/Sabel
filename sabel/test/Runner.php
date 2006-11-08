<?php

if (!defined('PHPUnit2_MAIN_METHOD'))
  define('PHPUnit2_MAIN_METHOD', 'Tester::main');

@require_once('PHPUnit2/TextUI/TestRunner.php');
@require_once('PHPUnit2/Framework/TestCase.php');

/**
 * Sabel specific Test_Runner inherit PHPUnit2_TextUI_TestRunner
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Runner extends PHPUnit2_TextUI_TestRunner
{
  public static function create()
  {
    return new self();
  }
  
  public static function running($controller, $module = null)
  {
    $testClassName = array();
    $testClassName[] = 'App';
    $testClassName[] = (!is_null($module)) ? ucfirst($module) : 'Index';
    $testClassName[] = 'Tests';
    $testClassName[] = ucfirst($controller);
    self::create()->start(array(join('_', $testClassName)));
  }
  
  public function start($arguments)
  {
    $test = (isset($arguments[0])) ? $arguments[0] : false;
    
    try {
      $this->doRun($this->getTest($test, $test . '.php'));
    } catch (Exception $e) {
      throw new Exception('Could not run test suite:'. $e->getMessage());
    }
  }
}
