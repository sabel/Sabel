<?php

/**
 * Test of Map_Selecter
 *
 * @category   Test
 * @package    test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Map_Selecter extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Selecter");
  }
  
  public function testToken()
  {
    $tokens = new Sabel_Map_Tokens("foo/user/login/");
    $this->assertEquals('foo',   $tokens->get(0));
    $this->assertEquals('user',  $tokens->get(1));
    $this->assertEquals('login', $tokens->get(2));
  }
  
  public function testOmittableAccept()
  {
    $s = new Sabel_Map_Selecter_Impl();
    $c = new Sabel_Map_Candidate();
    $c->addElement("test", Sabel_Map_Candidate::VARIABLE);
    $c->setOmittable("test");
    
    $result = $s->select(false, $c);
    
    $this->assertTrue($result);
  }
  
  public function testOmittableWithRequirementDeny()
  {
    $s = new Sabel_Map_Selecter_Impl();
    $c = new Sabel_Map_Candidate();
    
    $c->addElement("test", Sabel_Map_Candidate::VARIABLE);
    $c->setOmittable("test");
    $c->setRequirement("test", new Sabel_Map_Requirement_Regex("/([a-z].*)/"));
    
    $result = $s->select(false, $c);
    $this->assertTrue($result);
    
    $result = $s->select("12345", $c);
    $this->assertFalse($result);
  }
  
  public function testHasRequirementAccept()
  {
    $s = new Sabel_Map_Selecter_Impl();
    $c = new Sabel_Map_Candidate();
    
    $c->addElement("test", Sabel_Map_Candidate::VARIABLE);
    $c->setRequirement("test", new Sabel_Map_Requirement_Regex("/([a-z].*)/"));
    
    $result = $s->select("abcdefg", $c);
    $this->assertTrue($result);
  }
  
  public function testConstantWithRequirementException()
  {
    $s = new Sabel_Map_Selecter_Impl();
    $c = new Sabel_Map_Candidate();
    
    $c->addElement("test", Sabel_Map_Candidate::CONSTANT);
    try {
      $c->setRequirement("test", new Sabel_Map_Requirement_Regex("/([a-z].*)/"));
      $this->fail("does not throw Exception");
    } catch (Sabel_Map_Candidate_IllegalSetting $e) {
      if (!$e instanceof Sabel_Map_Candidate_IllegalSetting) $this->fail();
      $this->assertTrue(true);
    }
  }
  
  public function testRequirementWithConstantException()
  {
    $s = new Sabel_Map_Selecter_Impl();
    $c = new Sabel_Map_Candidate();
    
    $c->addElement("test", Sabel_Map_Candidate::VARIABLE);
    $c->setRequirement("test", new Sabel_Map_Requirement_Regex("/([a-z].*)/"));
    
    try {
      $c->setConstant("test");
      $this->fail("does not throw Exception");
    } catch (Sabel_Map_Candidate_IllegalSetting $e) {
      if (!$e instanceof Sabel_Map_Candidate_IllegalSetting) $this->fail();
      $this->assertTrue(true);
    }
  }
  
  public function testMatchToWildCard()
  {
    $s = new Sabel_Map_Selecter_Impl();
    $c = new Sabel_Map_Candidate();
    
    $c->addElement("test");
    $c->setMatchAll("test", true);
    
    $this->assertTrue($s->select("abcd", $c));
  }
}