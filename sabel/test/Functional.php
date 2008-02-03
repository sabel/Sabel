<?php

if (!defined("PHPUnit_MAIN_METHOD")) {
  define("PHPUnit_MAIN_METHOD", "Tester::main");
}

require_once("PHPUnit/TextUI/TestRunner.php");
require_once("PHPUnit/Framework/TestCase.php");

/**
 * functional test for Sabel Application
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Functional extends PHPUnit_Framework_TestCase
{
  protected function request(Sabel_Request $request, $storage = null)
  {
    $request = new Sabel_Request_Object($request);
    
    if ($storage === null) {
      $storage = new Sabel_Storage_InMemory();
    }
    
    $bus = new Sabel_Bus();
    $bus->set("request", $request);
    $bus->set("storage", $storage);
    $bus->run(new Config_Bus());
    
    return $bus->get("response");
  }
  
  protected function assertRedirect($uri, $toUri, $storage = null)
  {
    $response = $this->request($uri, $storage);
    
    if ($response->isRedirected()) {
      $this->assertEquals($toUri, $response->getLocationUri());
    } else {
      $this->fail("not redirected");
    }
    
    return $response;
  }
  
  public function eq($from, $to)
  {
    $this->assertEquals($from, $to);
  }
  
  public function neq($from, $to)
  {
    $this->assertNotEquals($from, $to);
  }
  
  protected function assertHtmlElementEquals($expect, $id, $html)
  {
    $doc = new DomDocument();
    @$doc->loadHTML($html);
    $element = $doc->getElementById($id);
    
    $this->assertEquals($expect, $element->nodeValue);
  }
}
