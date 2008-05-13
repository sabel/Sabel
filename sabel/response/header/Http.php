<?php

/**
 * Sabel_Response_Header_Http
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Header_Http
{
  public function output(Sabel_Response $response)
  {
    $httpVersion = "HTTP/1.0";
    
    header($httpVersion . " " . $response->getStatus()->toString());
    
    if ($response->hasHeaders()) {
      foreach ($response->getHeaders() as $message => $value) {
        header(ucfirst($message) . ": " . $value);
      }
    }
    
    if ($response->isRedirected()) {
      $location = $response->getLocation();
      l("redirect: $location");
      header("Location: $location");
    }
  }
}
