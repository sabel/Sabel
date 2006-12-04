<?php

/**
 * TestCase of Sabel_Map_Candidate
 *
 * @category   Test
 * @package    test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Map_Candidate extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Candidate");
  }
  
  public function setUp()
  {
  }
 
  public function tearDown()
  {
  }
  
  public function testName()
  {
    $c = new Sabel_Map_Candidate();
    $c->setName("default");
    $this->assertEquals("default", $c->getName());
  }
}