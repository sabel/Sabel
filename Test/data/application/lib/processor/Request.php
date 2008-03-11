<?php

class TestProcessor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if ($bus->has("request")) return;
    
    $request = new Sabel_Request_Object($this->getUri());
    $request->setGetValues($_GET);
    $request->setPostValues($_POST);
    
    if (isset($_SERVER["REQUEST_METHOD"])) {
      $request->method($_SERVER["REQUEST_METHOD"]);
    }
    
    $httpHeaders = array();
    foreach ($_SERVER as $key => $val) {
      if (strpos($key, "HTTP") === 0) {
        $httpHeaders[$key] = $val;
      }
    }
    
    $request->setHttpHeaders($httpHeaders);
    $bus->set("request", $request);
  }
  
  protected function getUri()
  {
    if (isset($_SERVER["REQUEST_URI"])) {
      $uri = trim(preg_replace("/\/{2,}/", "/", $_SERVER["REQUEST_URI"]), "/");
      $parsedUrl = parse_url("http://localhost/{$uri}");
      
      if (isset($parsedUrl["path"])) {
        return ltrim($parsedUrl["path"], "/");
      }
    }
    
    return "";
  }
}
