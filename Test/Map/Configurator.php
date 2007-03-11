<?php

/**
 * 
 *
 * @category   
 * @package    org.sabel.
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Test_Map_Configurator extends SabelTestCase
{
  public static function suite()
  {
    return self::createSuite("Test_Map_Configurator");
  }
  
  public function setUp()
  {
  }
 
  public function tearDown()
  {
  }
  
  public function testConfig()
  {
    Sabel_Map_Configurator::addCandidate("default",
                        ":controller/:action/:id",
                        array('default' => array(':id' => null),
                              ':module'  => 'index')
                        );
                        
    $candidate = Sabel_Map_Configurator::getCandidate("default");
    $this->assertEquals("default", $candidate->getName());
    
    $s = new Sabel_Map_Selecter_Impl();
    $tokens = new Sabel_Map_Tokens("blog/show");
    $results = array();
    foreach ($candidate as $current) {
      $results[] = $s->select($tokens->current(), $current);
      $tokens->next();
    }
    
    $this->assertTrue(!in_array(false, $results));
  }
  
  public function testConfigDefaultValue()
  {
    Sabel_Map_Configurator::addCandidate("default",
                        ":controller/:action/:id",
                        array('default' => array(':id' => 12),
                              ':module'  => 'index')
                        );
                        
    $candidate = Sabel_Map_Configurator::getCandidate("default");
    $this->assertEquals("default", $candidate->getName());
    
    $s = new Sabel_Map_Selecter_Impl();
    $tokens = new Sabel_Map_Tokens("blog/show");
    $results = array();
    foreach ($candidate as $current) { $results[] = $s->select($tokens->current(), $current);
      $tokens->next();
    }
    
    $this->assertTrue(!in_array(false, $results));
    $this->assertEquals('blog', $candidate->getElementVariableByName('controller'));
    $this->assertEquals('show', $candidate->getElementVariableByName('action'));
    $this->assertEquals(12,     $candidate->getElementVariableByName('id'));
  }
  
  public function testCandidateFind()
  {
    Sabel_Map_Configurator::addCandidate("default",
                        ":controller/:action/:id",
                        array(':module'  => 'index')
                        );
    Sabel_Map_Configurator::addCandidate("second",
                        ":module/:controller/:action/:id"
                        );
    Sabel_Map_Configurator::addCandidate("third",
                        ":action/:id",
                        array(':module'  => 'index',
                              ':controller' => 'index')
                        );
                        
    $candidate = Sabel_Map_Configurator::getCandidate("default");
    try {
      $candidate->find(new Sabel_Map_Tokens('controller'));
      $this->fail('candidate found');
    } catch (Sabel_Map_Candidate_NotFound $e) {
      $this->assertTrue(true);
    }
    
    $matched = $candidate->find(new Sabel_Map_Tokens('module/controller/action/id'));
    $this->assertEquals('default', $matched->getName());

    $matched = $candidate->find(new Sabel_Map_Tokens('action/id'));
    $this->assertEquals('third', $matched->getName());
  }
}
