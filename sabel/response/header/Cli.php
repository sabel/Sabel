<?php

/**
 * Sabel_Response_Header_Cli
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Header_Cli
{
  public function output(Sabel_Response $response)
  {
    $headers = array();
    $httpVersion = "HTTP/1.0";
    
    $headers[] = $httpVersion . " " . $response->getStatus()->toString();
    
    if ($response->isRedirected()) {
      $headers[] = "Location: " . $response->getLocation();
    }
    
    if ($response->hasHeaders()) {
      foreach ($response->getHeaders() as $message => $value) {
        $headers[] = ucfirst($message) . ": " . $value;
      }
    }
    
    return $headers;
  }
}
