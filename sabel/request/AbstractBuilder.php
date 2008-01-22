<?php

/**
 * Abstract Request Builder
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Request_AbstractBuilder extends Sabel_Object
{
  abstract protected function setUri($request, $uri);
  abstract protected function setGetValues($request);
  abstract protected function setPostValues($request);
  
  public final function build($request, $uri = null)
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
    if ($uri === null) {
      $url = "http://localhost" . Sabel_Environment::get("REQUEST_URI");
    } else {
      $url = "http://localhost/";
    }
    
    $parsedUrl = parse_url($url);
    if (isset($parsedUrl["path"])) {
      return ltrim($parsedUrl["path"], "/");
    } else {
      return "";
    }
  }
}
