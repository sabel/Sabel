<?php

/**
 * TestCase for sabel.map.*
 *
 * @category  Map
 * @author    Mori Reo <mori.reo@sabel.jp>
 */
class Test_Map_Match extends SabelTestCase
{
  private $config = null;
  
  public static function suite()
  {
    return self::createSuite("Test_Map_Match");
  }
  
  public function setUp()
  {
    $this->config = new ConfigMap();
  }
  
  public function tearDown()
  {
    $this->config->clearCandidates();
  }
  
  public function testSimple()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    $c = $this->routing("test/test");
    $this->assertEquals("default", $c->getName());
  }
  
  public function testNoMatch()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    try {
      $c = $this->routing("test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testFailMismatchUriAndElement()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    try {
      $this->routing("test/test/test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testFailWithDefault()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    try {
      $this->routing("test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMatchWithDefault()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index")
         ->defaults(array(":controller" => "index", ":action" => "index"));
    
    $candidate = $this->routing("test");
    $destination = $candidate->getDestination();
    
    $this->assertEquals("default", $candidate->getName());
    $this->assertEquals("index", $destination->getAction());
    
    $this->assertTrue($candidate->getElementByName("action")->hasVariable());
    $this->assertEquals("index", $candidate->getElementByName("action")->variable);
  }
  
  public function testMatchWithDefaultPriority()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index")
         ->defaults(array(":action" => "index"));
    
    $candidate = $this->routing("test/test");
    $destination = $candidate->getDestination();
    
    $this->assertEquals("default", $candidate->getName());
    $this->assertEquals("test", $destination->getAction());
  }
  
  public function testMatchWithParameter()
  {
    $this->route("default")
         ->uri(":controller/:action/:param")
         ->module("index")
         ->requirements(array(":param" => "/[0-9]+$/"));

    $candidate = $this->routing("test/test/1000");
    
    $this->assertEquals("default", $candidate->getName());
    $this->assertEquals("1000", $candidate->getElementByName("param")->variable);
  }
  
  public function testMismatchWithParameter()
  {
    $this->route("default")
         ->uri(":controller/:action/:param")
         ->module("index")
         ->requirements(array(":param" => "/^[0-9]+$/"));
    
    try {
      $candidate = $this->routing("test/test/test");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMismatchWithMultipleParameter()
  {
    $this->route("default")
         ->uri(":controller/:action/:param/:param2")
         ->module("index")
         ->requirements(array(":param" => "/^[0-9]+$/", ":param2" => "/^[a-z]+$/"));
    
    try {
      $candidate = $this->routing("test/test/1000/1000");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMatchhWithMultipleParameterWithDefaultsWithRequirements()
  {
    $this->route("default")
         ->uri(":controller/:action/:param/:param2")
         ->module("index")
         ->requirements(array(":param" => "/^[0-9]+$/", ":param2" => "/^[a-z]+$/"))
         ->defaults(array(":param" => "100", ":param2" => "abc"));
    
    $candidate = $this->routing("test/test");
    $this->assertEquals("100" ,$candidate->getElementByName("param")->variable);
    $this->assertEquals("abc" ,$candidate->getElementByName("param2")->variable);
  }
  
  public function testMisMatchhWithMultipleParameterWithRequirements()
  {
    $this->route("default")
         ->uri(":controller/:action/:param/:param2")
         ->module("index")
         ->requirements(array(":param" => "/^[0-9]{1}$/", ":param2" => "/^[a-z]+$/"));
    
    try {
      $candidate = $this->routing("test/test/100/abc");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail("matched");
  }
  
  public function testMultipleRoutePriority()
  {
    $this->route("article")
         ->uri(":controller/:action/:year/:month/:day")
         ->module("index")
         ->requirements(array(":year"  => "/^[1-3][0-9]{3}$/",
                              ":month" => "/^[0-2][0-9]$/",
                              ":day"   => "/^[0-3][0-9]$/"));
    
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    $candidate = $this->routing("blog/article/2008/01/20");
    $this->assertEquals("article", $candidate->getName());
    
    $candidate = $this->routing("blog/article");
    $this->assertEquals("default", $candidate->getName());
    
    try {
      $candidate = $this->routing("blog/article/9999/99/99");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMultipleRoutePriorityWithDefault()
  {
    $this->route("article")
         ->uri(":controller/:action/:year/:month/:day")
         ->module("index")
         ->requirements(array(":year"  => "/^[1-3][0-9]{3}$/",
                              ":month" => "/^[0-2][0-9]$/",
                              ":day"   => "/^[0-3][0-9]$/"))
         ->defaults(array(":day" => "01"));
    
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index");
    
    $candidate = $this->routing("blog/article/2008/01");
    
    $this->assertEquals("article", $candidate->getName());
    $this->assertEquals("2008", $candidate->getElementByName("year")->variable);
    $this->assertEquals("01", $candidate->getElementByName("month")->variable);
    $this->assertEquals("01", $candidate->getElementByName("day")->variable);
    
    $candidate = $this->routing("blog/article");
    $this->assertEquals("default", $candidate->getName());
    
    try {
      $candidate = $this->routing("blog/article/9999/99/99");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testMultipleRoutePriorityWithAllDefault()
  {
    $this->route("article")
         ->uri(":controller/:action/:year/:month/:day")
         ->module("index")
         ->requirements(array(":year"  => "/^[1-3][0-9]{3}$/",
                              ":month" => "/^[0-2][0-9]$/",
                              ":day"   => "/^[0-3][0-9]$/"))
         ->defaults(array(":year" => "2008", ":month" => "01", ":day" => null));
    
    $this->route("default")
         ->uri(":controller/:action")
         ->module("index")
         ->defaults(array(":action" => "test"));
    
    $candidate = $this->routing("blog/article");
    
    $this->assertEquals("article", $candidate->getName());
    $this->assertEquals("2008", $candidate->getElementByName("year")->variable);
    $this->assertEquals("01", $candidate->getElementByName("month")->variable);
    $this->assertEquals(null, $candidate->getElementByName("day")->variable);
    
    $candidate = $this->routing("test");
    $this->assertEquals("default", $candidate->getName());
    
    try {
      $candidate = $this->routing("blog/article/9999/99/99");
    } catch (Exception $e) {
      return;
    }
    
    $this->fail();
  }
  
  public function testWithConstant()
  {
    $this->route("admin")
         ->uri("admin/:controller/:action")
         ->module("admin");
    
    $this->route("manage")
         ->uri("manage/:controller/:action")
         ->module("manage");
    
    $candidate = $this->routing("admin/test/test");
    $this->assertEquals("admin", $candidate->getName());
    
    $candidate = $this->routing("manage/test/test");
    $this->assertEquals("manage", $candidate->getName());
  }
  
  public function testWithConstantWithUnmatchedUri()
  {
    $this->route("admin")
         ->uri("admin/:controller/:action")
         ->module("admin");
    
    $this->route("manage")
         ->uri("manage/:controller/:action")
         ->module("manage");
    
    $candidate = $this->routing("admin/test/test/test/test");
    $this->assertEquals("admin", $candidate->getName());
    
    $candidate = $this->routing("manage/test/test/test/test/test/test");
    $this->assertEquals("manage", $candidate->getName());
  }
  
  public function testWithConstantWithDefaults()
  {
    $this->route("admin")
         ->uri("admin/:controller/:action/:param")
         ->module("admin")
         ->defaults(array(":param" => "param"));
    
    $this->route("manage")
         ->uri("manage/:controller/:action")
         ->module("manage");
    
    $candidate = $this->routing("admin/test/test");
    $this->assertEquals("admin", $candidate->getName());
    $this->assertEquals("test", $candidate->getElementByName("controller")->variable);
    $this->assertEquals("test", $candidate->getElementByName("action")->variable);
    $this->assertEquals("param", $candidate->getElementByName("param")->variable);
    
    $candidate = $this->routing("manage/test/test/test/test/test/test");
    $this->assertEquals("manage", $candidate->getName());
  }
  
  public function testExtension()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("admin");
    
    $candidate = $this->routing("index/test.html");
    $this->assertEquals("html", $candidate->getElementByName("action")->extension);
    $this->assertEquals("test", $candidate->getElementByName("action")->variable);
    
    $candidate = $this->routing("index/test.tar.gz");
    $this->assertTrue($candidate->getElementByName("action")->hasExtension());
    $this->assertEquals("tar.gz", $candidate->getElementByName("action")->extension);
    $this->assertEquals("test", $candidate->getElementByName("action")->variable);
  }
  
  public function testExtensionT()
  {
    $this->route("default")
         ->uri(":controller/:action/:param")
         ->module("admin");
    
    $candidate = $this->routing("index/test.html/param.html");
    $this->assertEquals("html", $candidate->getElementByName("action")->extension);
    $this->assertEquals("test", $candidate->getElementByName("action")->variable);
    
    $this->assertEquals("param", $candidate->getElementByName("param")->variable);
    $this->assertEquals("html", $candidate->getElementByName("param")->extension);
  }
  
  public function testArray()
  {
    $this->route("default")
         ->uri(":controller/:action/:array[]")
         ->module("admin");
    
    $candidate = $this->routing("index/test/param/value/value");
    
    $expected = array("param", "value", "value");
    $this->assertEquals($expected, $candidate->getElementByName("array")->variable);
  }
  
  public function testArrayPriority()
  {
    $this->route("array")
         ->uri(":controller/:action/:array[]")
         ->module("admin");
    
    $this->route("default")
         ->uri(":controller/:action")
         ->defaults(array(":action" => "test"))
         ->module("admin");
    
    $candidate = $this->routing("index/test");
    $this->assertEquals("default", $candidate->getName());
    
    $candidate = $this->routing("index");
    $this->assertEquals("default", $candidate->getName());
  }
  
  public function testArrayPriorityWithDefault()
  {
    $this->route("array")
         ->uri(":controller/:action/:array[]")
         ->defaults(array(":array" => array(0, 1)))
         ->module("admin");
    
    $this->route("default")
         ->uri(":controller/:action")
         ->module("admin");
    
    $candidate = $this->routing("index/test");
    $this->assertEquals("array", $candidate->getName());
  }
  
  public function testMatchAll()
  {
    $this->route("default")
         ->uri(":controller/:action")
         ->module("admin");
    
    $this->route("matchall")
         ->uri("*")
         ->module("module")
         ->controller("controller")
         ->action("action");
    
    $candidate = $this->routing("hoge/fuga/foo/bar/baz");
    $this->assertEquals("matchall", $candidate->getName());
    
    $destination = $candidate->getDestination();
    $this->assertEquals("module",     $destination->getModule());
    $this->assertEquals("controller", $destination->getController());
    $this->assertEquals("action",     $destination->getAction());
  }
  
  protected function route($name)
  {
    return $this->config->route($name);
  }
  
  protected function request($uri)
  {
    $builder = new Sabel_Request_Builder();
    $request = new Sabel_Request_Object();
    return $builder->build($request, $uri);
  }
  
  protected function routing($uri)
  {
    $validCandidate = null;
    
    foreach ($this->config->build() as $candidate) {
      if ($candidate->evaluate($this->request($uri))) {
        $validCandidate = $candidate;
        break;
      }
    }
    
    if ($validCandidate === null) {
      throw new Sabel_Exception_Runtime("");
    }
    
    return $validCandidate;
  }
}

class ConfigMap extends Sabel_Map_Configurator
{
  public function configure() {}
}
