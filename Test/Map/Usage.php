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
    $tokens = new Sabel_Map_Tokens("blog/user/const2/foobar/123");
    
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
    
    $this->assertEquals("user",   $c->getElementVariableByName('controller'));
    $this->assertEquals("foobar", $c->getElementVariableByName('userName'));
    $this->assertEquals("123",    $c->getElementVariableByName('id'));
  }
  
  public function testMultipleCandidates()
  {
    $tokens = new Sabel_Map_Tokens("users/show/12");
    
    // :controller/:action/:id
    $default = new Sabel_Map_Candidate("defualt");
    $default->addElement('controller', Sabel_Map_Candidate::CONTROLLER);
    $default->addElement('action',     Sabel_Map_Candidate::ACTION);
    $default->addElement('id');
    $default->setOmittable('id');
    
    // :action/:year/:month/:day
    $blog = new Sabel_Map_Candidate("blog");
    $blog->addElement('action', Sabel_Map_Candidate::ACTION);
    $blog->addElement('year');
    $blog->setOmittable('year');
    $blog->setRequirement('year', new Sabel_Map_Requirement_Regex('/20[0-9]/'));
    $blog->addElement('month');
    $blog->setOmittable('month');
    $blog->addElement('day');
    $blog->setOmittable('day');
    
    $selecter = new Sabel_Map_Selecter_Impl();
    $results = array();
    
    foreach (array($blog, $default) as $candidate) {
      foreach ($candidate as $element) {
        $result = $selecter->select($tokens->current(), $element);
        
        // found unmatch. skip compare
        if ($result === false) {
          $results[] = false;
          break 1;
        }
        
        $tokens->next();
      }
      
      if (!in_array(false, $results)) {
        // candidate is match we finish compare with uri
        $matchedCandidate = $candidate;
        break 1;
      } else {
        // does't match initialize temporary variables
        $tokens->rewind();
        $results = array();
      }
    }
    
    $this->assertEquals('defualt', $matchedCandidate->getName());
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
  
  public function testUseWildCard()
  {
    $tokens = new Sabel_Map_Tokens("blog/test/adfkaa/a/aff/ff/ff/ff");
    $s = new Sabel_Map_Selecter_Impl();
    
    $c = new Sabel_Map_Candidate();
    $c->setName("wild");
    
    $c->addElement("blog", Sabel_Map_Candidate::CONSTANT);
    $c->addElement("wildcard");
    $c->setMatchAll("wildcard", true);
    
    $this->assertFalse($c->isMatchAll());
    $this->assertTrue($s->select($tokens->current(), $c->current()));
    $tokens->next();
    $c->next();
    
    $this->assertTrue($c->isMatchAll(), '$c->isMatchAll must be true');
    $this->assertTrue($s->select($tokens->current(), $c->current()));
  }
}