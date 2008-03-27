<?php

/**
 * Sabel_Test_UnitSuite
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_UnitSuite extends Sabel_Test_TestSuite
{
  public function add($testName)
  {
    $dir = RUN_BASE . DS . "tests" . DS . "unit" . DS;
    
    $parts = explode("_", $testName);
    $last = array_pop($parts);
    
    if (count($parts) > 0) {
      $dir .= strtolower(implode(DS, $parts)) . DS;
    }
    
    $className = "Unit_" . $testName;
    Sabel::fileUsing($dir . $last . ".php", true);
    
    $reflection = new ReflectionClass($className);
    if ($reflection->isSubClassOf("Sabel_Test_TestSuite")) {
      $this->addTest($reflection->getMethod("suite")->invoke(null));
    } else {
      $this->addTest(new self($className));
    }
  }
}
