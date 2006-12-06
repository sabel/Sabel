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
class Test_Map_Configurator extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Configurator");
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
                              'module'  => 'index')
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
                              'module'  => 'index')
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
    $this->assertEquals('blog', $candidate->getElementVariableByName('controller'));
    $this->assertEquals('show', $candidate->getElementVariableByName('action'));
    $this->assertEquals(12,     $candidate->getElementVariableByName('id'));
  }
}