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
      if ($method === "downFixture") {
        $annotation[0] = array_reverse($annotation[0]);
      }
      
      try {
        foreach ($annotation[0] as $fixtureName) {
          Sabel::fileUsing($fixtureDir . DS . $this->getFixturePath($fixtureName), true);
          $className = "Fixture_" . $fixtureName;
          $fixture = new $className();
          $fixture->$method();
        }
      } catch (Exception $e) {
        if ($reflection->hasMethod($method . "Exception")) {
          $reflection->getMethod($method . "Exception")->invoke(null, $e);
        } else {
          throw $e;
        }
      }
    }
  }
  
  protected function getFixturePath($fixtureName)
  {
    $exp = explode("_", $fixtureName);
    
    if (count($exp) === 1) {
      $path = $exp[0] . ".php";
    } else {
      $class = array_pop($exp);
      $prePath = implode("/", array_map("lcfirst", $exp));
      $path = $prePath . DIRECTORY_SEPARATOR . $class . ".php";
    }
    
    return $path;
  }
}
