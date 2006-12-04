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
  
  // UriCandidateと、RequestUriを比較する
  public function testSelecter()
  {
    $tokens = new Sabel_Map_Tokens("blog/user/const2/foobar");
    
    $c = new Sabel_Map_Candidate();
    $c->setName('default');
    // uri: blog/:controller/show/:userName/:id/:date
    
    $c->addElement('blog', Sabel_Map_Candidate::CONSTANT);
    
    $c->addElement('controller', Sabel_Map_Candidate::CONTROLLER);
    
    $c->addElement('const2', Sabel_Map_Candidate::CONSTANT);
    
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
}