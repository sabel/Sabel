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
  protected function request(Sabel_Request $request, $session = null, $maxRedirects = 0)
  {
    if ($session === null) {
      $session = Sabel_Session_InMemory::create();
    }
    
    // @todo
    $_COOKIE[session_name()] = $session->getId();
    
    if ($maxRedirects > 0) {
      return $this->requestWithRedirect($request, $session, $maxRedirects);
    } else {
      $bus = new Sabel_Bus();
      $bus->set("request", $request);
      $bus->set("session", $session);
      $bus->run(new Config_Bus());
      
      return $bus->get("response");
    }
  }
  
  protected function httpGet($uri, $session = null, $maxRedirects = 0)
  {
    $uri = trim(preg_replace("@/{2,}@", "/", $uri, "/"));
    $parsedUrl = parse_url("http://localhost/{$uri}");
    $request = new Sabel_Request_Object(ltrim($parsedUrl["path"], "/"));
    
    if (isset($parsedUrl["query"]) && !empty($parsedUrl["query"])) {
      parse_str($parsedUrl["query"], $get);
      $request->setGetValues($get);
    }
    
    return $this->request($request, $session, $maxRedirects);
  }
  
  protected function requestWithRedirect($request, $session, $maxRedirects)
  {
    $responses = array();
    $response  = $this->request($request, $session, 0);
    $responses[] = $response;
    
    if (!$response->isRedirected()) return $responses;
    
    $redirectTo = $response->getLocationUri();
    for ($i = 0; $i < $maxRedirects; $i++) {
      $response = $this->httpGet($redirectTo, $session, 0);
      $responses[] = $response;
      
      if ($response->isRedirected()) {
        break;
      } else {
        $redirectTo = $response->getLocationUri();
      }
    }
    
    return $responses;
  }
  
  protected function isRedirected($response)
  {
    return $response->getStatus()->isRedirect();
  }
  
  protected function assertRedirect($uri, $toUri, $session = null)
  {
    $response = $this->request($uri, $session);
    
    if ($this->isRedirected($response)) {
      $this->assertEquals($toUri, $response->getLocationUri());
    } else {
      $this->fail("not redirected");
    }
    
    return $response;
  }
  
  protected function assertHtmlElementEquals($expect, $id, $html)
  {
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $element = $doc->getElementById($id);
    
    $this->assertEquals($expect, $element->nodeValue);
  }
}
