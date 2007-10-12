<?php

/**
 * TestCase of usage sabel map
 *
 * @todo add many many many test cases
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Map_Usage extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Map_Usage");
  }
  
  public function setUp()
  {
  }
 
  public function tearDown()
  {
  }
  
  public function testEvaluteFail()
  {
    // :action/:year/:month/:day
    $blog = new Sabel_Map_Candidate("blog");
    $blog->route(":action/:year/:month/:day");
    $blog->setRequirement("year", new Sabel_Map_Requirement_Regex("/20[0-9]/"));
    $blog->setOmittables(array("year", "month", "day"));
    
    $this->assertFalse($blog->evalute(explode("/", "test/test/1")));
  }
  
  public function testStandard()
  {
    // :controller/:action/:id
    $default = new Sabel_Map_Candidate("defualt");
    $default->route(":controller/:action/:id");
    $default->setOmittable("id");
    
    $default->evalute(explode("/", "index/toppage"));
    
    $c = $default->getElementByName("controller");
    $this->assertEquals("index", $c->variable);
    
    $a = $default->getElementByName("action");
    $this->assertEquals("toppage", $a->variable);
    $this->assertFalse($a->hasExtension());
    
    $this->assertTrue($default->hasAction());
    $this->assertTrue($default->hasAction());
  }
  
  public function testArray()
  {
    $default = new Sabel_Map_Candidate("defualt");
    $default->route(":directories[]/:action");
    
    $default->evalute(explode("/", "a/b/c/d"));
    
    $d = $default->getElementByName("directories");
    $this->assertEquals(array("a", "b", "c", "d"), $d->variable);
  }
  
  public function testConstant()
  {
    $default = new Sabel_Map_Candidate("defualt");
    $default->route("const/:controller/:action");
    $opt = array("default"=>array(":controller" => "index",
                                  ":action"     => "index"));
    $default->setOptions($opt);
    
    $default->evalute(explode("/", "const/cont/act"));
    
    $c = $default->getElementByName("controller");
    $this->assertEquals("cont", $c->variable);
    $this->assertEquals("cont", $default->getController());
  }
  
  public function testConstantWithDefault()
  {
    $default = new Sabel_Map_Candidate("defualt");
    $default->route("const/:controller/:action");
    $opt = array("default"=>array(":controller" => "index",
                                  ":action"     => "index"));
    $default->setOptions($opt);
    
    $default->evalute(explode("/", "const"));
    
    $c = $default->getElementByName("controller");
    $this->assertEquals("index", $c->variable);
    $this->assertEquals("index", $default->getController());
    
    $a = $default->getElementByName("action");
    $this->assertEquals("index", $a->variable);
    $this->assertEquals("index", $default->getAction());
  }
  
  public function testArrayWithEndSpecificDirective()
  {
    $default = new Sabel_Map_Candidate("defualt");
    $default->route(":directories[]/:action.html");
    
    $default->evalute(explode("/", "a/b/c/d.html"));
    
    $d = $default->getElementByName("directories");
    $this->assertEquals(array("a", "b", "c"), $d->variable);
    
    $a = $default->getElementByName("action");
    $this->assertEquals("d", $a->variable);
    $this->assertEquals("html", $a->extension);
  }
  
  public function testEndSpecificDirective()
  {
    $default = new Sabel_Map_Candidate("default");
    $default->route(":controller/:action.html/:variable.jpg");
    
    $default->evalute(explode("/", "ctrl/action.html/variable.jpg"));
    
    $a = $default->getElementByName("action");
    $this->assertEquals("action", $a->variable);
    $this->assertEquals("html", $a->extension);
    
    $v = $default->getElementByName("variable");
    $this->assertEquals("variable.jpg", $v->variable);
    $this->assertEquals("jpg", $v->extension);
  }
  
  public function testNotEndSpecificDirective()
  {
    $default = new Sabel_Map_Candidate("default");
    $default->route(":controller/:action/:variable");
    
    $resEvalute = $default->evalute(explode("/", "ctrl/action/variable.html"));
    
    $a = $default->getElementByName("action");
    $this->assertEquals("action", $a->variable);
    
    $v = $default->getElementByName("variable");
    $this->assertEquals("variable.html", $v->variable);
    $this->assertEquals("html", $v->extension);
  }
  
  public function testEndSpecificDirectiveFail()
  {
    $default = new Sabel_Map_Candidate("default");
    $default->route(":controller/:action/:variable.jpg");
    
    $resEvalute = $default->evalute(explode("/", "ctrl/action/variable.html"));
    $this->assertFalse($resEvalute);
  }
  
  public function testUseWildCard()
  {
    $c = new Sabel_Map_Candidate("wild");
    $c->route("blog/:all");
    $c->setMatchAll("all", true);
  }
}
