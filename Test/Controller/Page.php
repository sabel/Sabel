<?php

/**
 * test for sabel.controller.Page
 *
 * @category Controller
 * @author   Ebine Yutaka <ebine.yutaka@sabel.jp>
 */
class Test_Controller_Page extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Controller_Page");
  }
  
  public function testIndexAction()
  {
    $c = $this->createController();
    $c->initialize();
    $c->setAction("index");
    $c->execute();
    
    $this->assertTrue($c->isExecuted());
    $this->assertEquals("index", $c->getAction());
    $this->assertEquals("index", $c->getAttribute("actionResult"));
  }
  
  public function testReservedAction()
  {
    $c = $this->createController();
    $c->setAction("getRequest");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
    $this->assertTrue($c->getResponse()->isNotFound());
  }
  
  public function testHiddenAction()
  {
    $c = $this->createController();
    $c->setAction("hiddenAction");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
    $this->assertTrue($c->getResponse()->isNotFound());
  }
  
  public function testProtectedAction()
  {
    $c = $this->createController();
    $c->setAction("hoge");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
  }
  
  public function testAttribute()
  {
    $c = $this->createController();
    $c->setAttribute("a", "10");
    $c->setAttribute("b", "20");
    $this->assertEquals("10", $c->getAttribute("a"));
    $this->assertEquals("20", $c->getAttribute("b"));
    $this->assertEquals(null, $c->getAttribute("c"));
  }
  
  public function testAttributes()
  {
    $c = $this->createController();
    $c->setAttributes(array("a" => "10", "b" => "20"));
    $this->assertEquals("10", $c->getAttribute("a"));
    $this->assertEquals("20", $c->getAttribute("b"));
    $this->assertEquals(null, $c->getAttribute("c"));
    
    $expected = array("a" => "10", "b" => "20");
    $this->assertEquals($expected, $c->getAttributes());
  }
  
  public function testIsAttributeSet()
  {
    $c = $this->createController();
    $c->setAttribute("a", "10");
    $this->assertTrue($c->isAttributeSet("a"));
    $c->setAttribute("b", null);
    $this->assertFalse($c->isAttributeSet("b"));
  }
  
  public function testHasAttribute()
  {
    $c = $this->createController();
    $c->setAttribute("a", "10");
    $this->assertTrue($c->hasAttribute("a"));
    $c->setAttribute("b", null);
    $this->assertTrue($c->hasAttribute("b"));
  }
  
  public function testMagickMethods()
  {
    $c = $this->createController();
    $c->a = "10";
    $c->b = "20";
    $this->assertEquals("10", $c->a);
    $this->assertEquals("20", $c->b);
    $this->assertEquals(null, $c->c);
    $this->assertEquals("10", $c->getAttribute("a"));
    $this->assertEquals("20", $c->getAttribute("b"));
  }
  
  protected function createController()
  {
    return new TestController(new Sabel_Response_Object());
  }
}

class TestController extends Sabel_Controller_Page
{
  protected $hidden = array("hiddenAction");
  
  public function index()
  {
    $this->actionResult = "index";
  }
  
  public function hiddenAction() {}
  protected function hoge() {}
}
