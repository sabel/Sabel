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
class Test_Map_Scanner extends PHPUnit2_Framework_TestCase
{
  public static function suite()
  {
    return new PHPUnit2_Framework_TestSuite("Test_Map_Scanner");
  }
  
  public function setUp()
  {
  }
 
  public function tearDown()
  {
  } 
  
  public function testScanner()
  {
    
  }
}

interface Sabel_Map_Scanner
{
  public function scan($string);
}

class Sabel_Map_Scanner_String implements Sabel_Map_Scanner
{
  public function scan($string)
  {
    
  }
}