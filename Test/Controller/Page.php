<?php

require_once ("MockRequest.php");
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
  private $storage = null;
  
  public function setUp()
  {
    $this->storage = new Sabel_Storage_InMemory();
    $this->c = new PageControllerForTest();
    $this->assertTrue(is_object($this->c));
    $this->c->setup(new MockRequest(), $this->storage);
  }
 
  public function tearDown()
  {
  }
  
  public function testExecuteSimpleAction()
  {
    $this->c->execute("testAction");
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
    $this->c->setup($request);
    $this->c->execute("testActionWithParameter");
  }
}


class EventListener
{
  public function notify($controller, $state)
  {
    echo $state;
  }
}