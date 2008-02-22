<?php

class TestProcessor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if (!$bus->has("request")) {
      $bus->set("request", $this->createRequestObject());
    }
  }
  
  protected function createRequestObject()
  {
    $request = new Sabel_Request_Object();
    
    $uri = Sabel_Environment::get("REQUEST_URI");
    $uri = trim(preg_replace("/\/{2,}/", "/", $uri), "/");
    $parsedUrl = parse_url("http://localhost/{$uri}");
    
    if (isset($parsedUrl["path"])) {
      $request->setUri(ltrim($parsedUrl["path"], "/"));
    }
    
    $request->setGetValues($_GET);
    $request->setPostValues($_POST);
    $request->method(Sabel_Environment::get("REQUEST_METHOD"));
    
    return $request;
  }
}
