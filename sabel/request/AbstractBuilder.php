<?php

abstract class Sabel_Request_AbstractBuilder
{
  public final function build($request, $uri)
  {
    list($uri, $params) = $this->divideUriAndParameter($uri);
    $this->setUri($request, $uri);
    $this->setParameters($request, $params);
    $this->setGetValues($request);
    $this->setPostValues($request);
    $this->setParameterValues($request);  
    return $request;
  }
  
  protected function divideUriAndParameter($uri)
  {
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
}
