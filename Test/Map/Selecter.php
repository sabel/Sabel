<?php

/**
 * Test_Map_Element
 * 
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
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
    $this->assertEquals('foo', $tokens->get(0));
    $this->assertEquals('user', $tokens->get(1));
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
}