<?php

require_once ("PHPUnit/Framework/TestCase.php");

/**
 * functional test for Sabel Application
 *
 * @category   Test
 * @package    org.sabel.test
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Test_Functional extends Sabel_Test_TestCase
{
  protected function request(Sabel_Request $request, $session = null)
  {
    if ($session === null) {
      $session = Sabel_Session_InMemory::create();
    }
    
    // @todo
    $_COOKIE[session_name()] = $session->getId();
    
    $bus = new Sabel_Bus();
    $bus->set("request", $request);
    $bus->set("session", $session);
    $bus->run(new Config_Bus());
    
    return $bus->get("response");
  }
  
  protected function httpGet($uri, $session = null)
  {
    $uri = trim(preg_replace("@/{2,}@", "/", $uri, "/"));
    $parsedUrl = parse_url("http://localhost/{$uri}");
    $request = new Sabel_Request_Object(ltrim($parsedUrl["path"], "/"));
    
    if (isset($parsedUrl["query"]) && !empty($parsedUrl["query"])) {
      foreach (explode("&", $parsedUrl["query"]) as $param) {
        list ($k, $v) = explode("=", $param);
        $request->value($k, $v);
      }
    }
    
    return $this->request($request, $session);
  }
  
  protected function assertRedirect($uri, $toUri, $session = null)
  {
    $response = $this->request($uri, $session);
    
    if ($response->isRedirected()) {
      $this->assertEquals($toUri, $response->getLocationUri());
    } else {
      $this->fail("not redirected");
    }
    
    return $response;
  }
  
  protected function assertHtmlElementEquals($expect, $id, $html)
  {
    $doc = new DomDocument();
    @$doc->loadHTML($html);
    $element = $doc->getElementById($id);
    
    $this->assertEquals($expect, $element->nodeValue);
  }
}
