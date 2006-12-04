<?php

/**
 * TestCase of usage sabel map
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Map_Usage extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Usage");
  }
  
  public function setUp()
  {
  }
 
  public function tearDown()
  {
  }
  
  public function testSuccess()
  {
    $tokens = new Sabel_Map_Tokens("blog/user/const2/foobar");
    
    $c = new Sabel_Map_Candidate();
    $c->setName('default');
    
    $c->addElement('blog',       Sabel_Map_Candidate::CONSTANT);
    $c->addElement('controller', Sabel_Map_Candidate::CONTROLLER);
    $c->addElement('const2',     Sabel_Map_Candidate::CONSTANT);
    
    $c->addElement('userName');
    $c->setRequirement('userName', new Sabel_Map_Requirement_Regex('/([a-zA-Z].*)/'));
    
    $c->addElement('id');
    $c->setOmittable('id');
    $c->setRequirement('id', new Sabel_Map_Requirement_Regex('/([0-9].*)/'));
    
    $c->addElement('date');
    $c->setOmittable('date');

    $s = new Sabel_Map_Selecter_Impl();
    
    foreach ($c as $current) {
      $result = $s->select($tokens->current(), $current);
      $this->assertTrue($result);
      $tokens->next();
    }
  }
  
  public function testFail()
  {
    $tokens = new Sabel_Map_Tokens("blog/foo");
    
    $c = new Sabel_Map_Candidate();
    $c->setName("default");
    
    $c->addElement("blog", Sabel_Map_Candidate::CONSTANT);
    $c->addElement("user");
    $c->addElement("option");
    
    $s = new Sabel_Map_Selecter_Impl();
    
    foreach ($c as $currentCandidate) {
      $result = $s->select($tokens->current(), $currentCandidate);
      $tokens->next();
    }
    
    $this->assertFalse($result);
  }
}