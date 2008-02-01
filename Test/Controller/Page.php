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
  
  public function testNotSetup()
  {
    try {
      $this->createController()->execute("index");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testSetup()
  {
    $c = $this->setUpController($this->createController());
    $this->assertTrue($c instanceof TestController);
  }
  
  public function testObjects()
  {
    $c = $this->setUpController($this->createController());
    $this->assertTrue($c->getResponse()   instanceof Sabel_Response);
    $this->assertTrue($c->getRequest()    instanceof Sabel_Request);
    $this->assertTrue($c->getRedirector() instanceof Sabel_Controller_Redirector);
    $this->assertNull($c->getStorage());
  }
  
  public function testIndexAction()
  {
    $c = $this->setUpController($this->createController());
    $c->initialize();
    $c->setAction("index");
    $c->execute();
    
    $this->assertTrue($c->isExecuted());
    $this->assertEquals("index", $c->getAction());
    $this->assertEquals("index", $c->getAttribute("actionResult"));
  }
  
  public function testReservedAction()
  {
    $c = $this->setUpController($this->createController());
    $c->setAction("getRequest");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
    $this->assertTrue($c->getResponse()->isNotFound());
  }
  
  public function testHiddenAction()
  {
    $c = $this->setUpController($this->createController());
    $c->setAction("hiddenAction");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
    $this->assertTrue($c->getResponse()->isNotFound());
  }
  
  public function testProtectedAction()
  {
    $c = $this->setUpController($this->createController());
    $c->setAction("hoge");
    $c->execute();
    
    $this->assertFalse($c->isExecuted());
  }
  
  public function testIsRedirected()
  {
    $c = $this->setUpController($this->createController());
    $c->setAction("test");
    $c->execute();
    
    $this->assertTrue($c->isExecuted());
    $this->assertTrue($c->isRedirected());
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
  
  protected function setUpController($c)
  {
    $c->setUp(new RequestObjectMock(""), new Sabel_Controller_Redirector());
    return $c;
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
  
  public function test()
  {
    $this->redirect->url("index/index");
  }
  
  public function hiddenAction() {}
  protected function hoge() {}
}

class RequestObjectMock extends Sabel_Object implements Sabel_Request
{
  private $uri = null;
  
  private
    $getValues       = array(),
    $postValues      = array(),
    $parameterValues = array();
    
  private $method = Sabel_Request::GET;
  
  public function __construct($uri = null) {}
  public function to($uri) {}
  public function get($uri) {}
  public function post($uri) {}
  public function put($uri) {}
  public function delete($uri) {}
  public function getUri()    { return null;    }
  public function toArray()   { return array(); }
  public function isPost()    { return ($this->method === Sabel_Request::POST);   }
  public function isGet()     { return ($this->method === Sabel_Request::GET);    }
  public function isPut()     { return ($this->method === Sabel_Request::PUT);    }
  public function isDelete()  { return ($this->method === Sabel_Request::DELETE); }
  public function getMethod() { return $this->method; }
  
  public function method($method)
  {
    $this->method = $method;
    
    return $this;
  }
  
  public function setGetValue($key, $value)
  {
    $this->getValues[$key] = $value;
  }
  
  public function setGetValues(array $values)
  {
    $this->getValues = $values;
  }
  
  public function fetchGetValues()
  {
    if (count($this->getValues) === 0) return array();
    
    foreach ($this->getValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->getValues;
  }
  
  public function fetchGetValue($key)
  {
    if (array_key_exists($key, $this->getValues)) {
      return $this->getValues[$key];
    } else {
      return null;
    }
  }
  
  public function setPostValue($key, $value)
  {
    $this->postValues[$key] = $value;
  }
  
  public function setPostValues(array $values)
  {
    $this->postValues = $values;
  }
  
  public function fetchPostValue($key)
  {
    if (array_key_exists($key, $this->postValues)) {
      $value = $this->postValues[$key];
      return ($value === "") ? null : $value;
    } else {
      return null;
    }
  }
  
  public function fetchPostValues()
  {
    if (count($this->postValues) === 0) return array();
    
    foreach ($this->postValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->postValues;
  }
  
  public function setParameterValue($key, $value)
  {
    $this->parameterValues[$key] = $value;
    
    return $this;
  }
  
  public function setParameterValues(array $values)
  {
    $this->parameterValues = $values;
    
    return $this;
  }
  
  public function fetchParameterValue($key)
  {
    if (array_key_exists($key, $this->parameterValues)) {
      $value = $this->parameterValues[$key];
      return ($value === "") ? null : $value;
    } else {
      return null;
    }
  }
  
  public function fetchParameterValues()
  {
    if (count($this->parameterValues) === 0) return array();
    
    foreach ($this->parameterValues as &$value) {
      if ($value === "") $value = null;
    }
    
    return $this->parameterValues;
  }
}
