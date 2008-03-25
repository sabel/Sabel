<?php

/**
 * Sabel_Test_TestSuite
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_TestSuite extends PHPUnit_Framework_TestSuite
{
  public function setUp()
  {
    $this->doFixture("upFixture");
  }
  
  public function tearDown()
  {
    $this->doFixture("downFixture");
  }
  
  protected function doFixture($method)
  {
    $name = ($this->name === "") ? get_class($this) : $this->name;
    
    $fixtureDir = RUN_BASE . DS . "tests" . DS . "fixture";
    $reflection = new Sabel_Reflection_Class($name);
    $annotation = $reflection->getAnnotation("fixture");
    
    if (isset($annotation[0])) {
      foreach ($annotation[0] as $fixtureName) {
        Sabel::fileUsing($fixtureDir . DS . $fixtureName . ".php", true);
        $className = "Fixture_" . $fixtureName;
        $fixture = new $className();
        $fixture->$method();
      }
    }
  }
}
