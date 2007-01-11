<?php

Sabel::using("Sabel_Request");
require_once ("PageControllerForTest.php");

/**
 * 
 * @category   
 * @package    org.sabel.
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Controller_Page extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Controller_Page");
  }
  
  private $c = null;
  
  public function setUp()
  {
    $this->c = new PageControllerForTest();
    $this->assertTrue(is_object($this->c));
    $this->c->setup(new MockRequest(), null, 'mock');
  }
 
  public function tearDown()
  {
  }
  
  public function testExecuteSimpleAction()
  {
    $result = $this->c->execute("testAction");
    $this->assertEquals("test", $result["test"]);
  }
  
  public function testInvalidAction()
  {
    try {
      $result = $this->c->execute("");
      $this->fail("exception not ocrred");
    } catch (Exception $e) {
      $this->assertTrue(true);
    }
  }
  
  public function testActionWithParameter()
  {
    $request = new MockRequest();
    $this->c->setup($request, null, 'mock');
    $result = $this->c->execute("testActionWithParameter");
    $this->assertEquals("testParam", $result["test"]);
  }
}

class MockRequest extends Sabel_Object implements Sabel_Request
{
  public function getPostRequests()
  {
    return array();
  }
  
  public function __toString()
  {
    
  }
  
  public function getParameters()
  {
    return new StdClass();
  }
  
  public function hasParameter($name)
  {
    return true;
  }
  
  public function getParameter($name)
  {
    return "testParam";
  }
}
