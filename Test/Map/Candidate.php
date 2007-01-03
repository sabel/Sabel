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
    return new PHPUnit_Framework_TestSuite("Test_Map_Candidate");
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
  
  public function testUri()
  {
    $defc = new Sabel_Map_Candidate("default");
    $defc->addElement('controller', Sabel_Map_Candidate::CONTROLLER);
    $defc->addElement('action',     Sabel_Map_Candidate::ACTION);
    $defc->addElement('id');
    $defc->setOmittable('id');
    
    $selecter = new Sabel_Map_Selecter_Impl();
    $tokens = new Sabel_Map_Tokens("blog/show");
    foreach ($defc as $current) {
      $selecter->select($tokens->current(), $current);
      $tokens->next();
    }
    
    $result = $defc->uri();
    $this->assertEquals("blog/show", $result);
    
    $result = $defc->uri(array("a"=>"delete"));
    $this->assertEquals("blog/delete", $result);
    
    $result = $defc->uri(array(":action"=>"delete", 'id'=>123));
    $this->assertEquals("blog/delete/123", $result);
    
    $result = $defc->uri(array(':controller'=>'bbs',':action'=>'delete'));
    $this->assertEquals("bbs/delete", $result);
    
    $result = $defc->uri(array(':module'=>'index',':action'=>'delete'));
    $this->assertEquals("blog/delete", $result);
  }
}
