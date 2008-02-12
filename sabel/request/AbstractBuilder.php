<?php

/**
 * Abstract Request Builder
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Request_AbstractBuilder extends Sabel_Object
{
  abstract protected function setUri(Sabel_Request $request, $uri);
  abstract protected function setGetValues(Sabel_Request $request);
  abstract protected function setPostValues(Sabel_Request $request);
  
  public final function build(Sabel_Request $request, $uri = null)
  {
    $this->setMethod($request);
    $this->setUri($request, $this->createUri($uri));
    $this->setGetValues($request);
    $this->setPostValues($request);
    
    return $request;
  }
  
  protected function setMethod($request)
  {
    if (isset($_SERVER["REQUEST_METHOD"])) {
      $request->method($_SERVER["REQUEST_METHOD"]);
    }
  }
  
  protected function createUri($uri = null)
  {
    $host = Sabel_Environment::get("HTTP_HOST");
    
    if ($uri === null) {
      $url = "http://{$host}/" . ltrim(Sabel_Environment::get("REQUEST_URI"), "/");
    } else {
      $url = "http://{$host}/" . ltrim($uri, "/");
    }
    
    $parsedUrl = parse_url($url);
    
    if (isset($parsedUrl["path"])) {
      return ltrim($parsedUrl["path"], "/");
    } else {
      return "";
    }
  }
}
