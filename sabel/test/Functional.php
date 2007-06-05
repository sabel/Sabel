<?php

if (!defined('PHPUnit_MAIN_METHOD'))
  define('PHPUnit_MAIN_METHOD', 'Tester::main');

require_once ('PHPUnit/TextUI/TestRunner.php');
require_once ('PHPUnit/Framework/TestCase.php');

/**
 * functional test for Sabel Application
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Functional extends PHPUnit_Framework_TestCase
{
  protected $storage = null;
  
  protected function request($uri, $storage = null)
  {
    $aFrontController = new Sabel_Controller_Front();
    
    if ($storage === null) {
      $storage = new Sabel_Storage_InMemory();
    }
    
    $this->storage = $storage;
    $aFrontController->ignition($uri, $storage);
    
    return $aFrontController->getResponse();
  }
  
  protected function assertRedirect($uri, $toUri)
  {
    $response = $this->request($uri);
    
    if ($response->isRedirected()) {
      $this->assertEquals($toUri, $response->getLocationUri());
    } else {
      $this->fail("not redirected");
    }
  }
  
  protected function assertAssigned($uri, $value)
  {
    $response = $this->request($uri);
    
  }
  
  protected function assertHtmlElementEquals($expect, $id, $html)
  {
    $doc = new DomDocument();
    @$doc->loadHTML($html);
    $element = $doc->getElementById($id);
    
    $this->assertEquals($expect, $element->nodeValue);
  }
}
