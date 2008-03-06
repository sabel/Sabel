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
    
    if ($response->hasHeaders()) {
      foreach ($response->getHeaders() as $message => $value) {
        $headers[] = ucfirst($message) . ": " . $value;
      }
    }
    
    if ($response->getLocation()) {
      l("redirect: " . var_export($response->getLocation(), 1));
    }
    
    if ($response->isRedirected()) {
      $headers[] = "Location: " . $response->getLocation();
    } elseif ($response->isNotFound()) {
      $headers[] = "HTTP/1.0 404 Not Found";
    } elseif ($response->isForbidden()) {
      $headers[] = "HTTP/1.0 403 Forbidden";
    } elseif ($response->isServerError()) {
      $headers[] = "HTTP/1.0 500 Internal Server Error";
    } elseif ($response->isNotModified()) {
      $headers[] = "HTTP/1.0 304 Not Modified";
    }
    
    return $headers;
  }
}
