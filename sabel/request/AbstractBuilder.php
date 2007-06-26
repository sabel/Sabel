<?php

/**
 * Abstract Request Builder
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Request_AbstractBuilder
{
  public final function build($request, $uri = null)
  {
    list($uri, $params) = $this->divideUriAndParameter($uri);
    
    $this->setMethod($request);
    $this->setUri($request, $uri);
    $this->setParameters($request, $params);
    $this->setGetValues($request);
    $this->setPostValues($request);
    $this->setParameterValues($request);
    $this->setHeaders($request);
    
    return $request;
  }
  
  protected function setMethod($request)
  {
    if (isset($_SERVER["REQUEST_METHOD"])) {
      $request->method($_SERVER["REQUEST_METHOD"]);
    }
  }
  
  protected function divideUriAndParameter($uri = null)
  {
    if ($uri === null) {
      $host = $_SERVER["HTTP_HOST"];
      $uri  = "http://" . $host . "/" . $_SERVER["REQUEST_URI"];
    }
    
    if ($uri === "/") {
      return array(null, null);
    }
    
    $parsedUri = parse_url($uri);
    
    $uri    = ltrim($parsedUri["path"], "/");
    $params = (isset($parsedUri["query"])) ? $parsedUri["query"] : "";
    
    return array($uri, $params);
  }
  
  abstract protected function setUri($request, $uri);
  
  abstract protected function setParameters($request, $parameters);
    
  abstract protected function setGetValues($request);
  
  abstract protected function setPostValues($request);
  
  abstract protected function setParameterValues($request);
  
  abstract protected function setHeaders($request);
}
