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
  
  public function testMatchTo()
  {    
    // :controller/:action/:id
    $default = new Sabel_Map_Candidate("defualt");
    $default->route(":controller/:action/:id");
    $default->setOmittable("id");
    
    // :action/:year/:month/:day
    $blog = new Sabel_Map_Candidate("blog");
    $blog->route(":action/:year/:month/:day");
    $blog->setRequirement("year", new Sabel_Map_Requirement_Regex("/20[0-9]/"));
    $blog->setOmittables(array("year", "month", "day"));
    
    $this->assertTrue($default->isMatch(explode("/", "test/test/1")));
  }
  
  public function testArray()
  {
    $default = new Sabel_Map_Candidate("defualt");
    $default->route(":directories[]/:action");
    
    $default->isMatch(explode("/", "a/b/c/d"));
    
    $d = $default->getElementByName("directories");
    $this->assertEquals(array("a", "b", "c", "d"), $d->variable);
  }
  
  public function testArrayWithEndSpecificDirective()
  {
    $default = new Sabel_Map_Candidate("defualt");
    $default->route(":directories[]/:action.html");
    
    $default->isMatch(explode("/", "a/b/c/d.html"));
    
    $d = $default->getElementByName("directories");
    $this->assertEquals(array("a", "b", "c"), $d->variable,
    "\n\n\n\n@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n\nthis fail i mean it. you can ignore this fail. as soon success. \n\n@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n\n\n\n");
  }
  
  public function testUseWildCard()
  {
    $c = new Sabel_Map_Candidate("wild");
    $c->route("blog/:all");
    $c->setMatchAll("all", true);
  }
}
