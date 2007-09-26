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
class Test_Map_Candidate extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Map_Candidate");
  }
  
  public function setUp()
  {
  }
 
  public function tearDown()
  {
  }
  
  public function testUri()
  {
    $defc = new Sabel_Map_Candidate("default");
    $defc->route(":controller/:action/:id");
    $defc->setOmittable("id");
    
    $result = $defc->uri(array(":controller" => "blog", "a" => "show"));
    $this->assertEquals("blog/show", $result);
    
    $result = $defc->uri(array(":controller" => "blog", "a" => "delete"));
    $this->assertEquals("blog/delete", $result);
    
    $result = $defc->uri(array(":action" => "delete", 'id' => 123));
    $this->assertEquals("delete/123", $result);
    
    $result = $defc->uri(array(':controller' => 'bbs',':action' => 'delete'));
    $this->assertEquals("bbs/delete", $result);
    
    $result = $defc->uri(array(':module' => 'index',':action' => 'delete'));
    $this->assertEquals("delete", $result);
  }
}
