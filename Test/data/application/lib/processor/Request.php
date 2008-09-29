<?php

class TestProcessor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if ($bus->has("request")) {
      $request = $bus->get("request");
    } else {
      $uri = "";
      if (isset($_SERVER["REQUEST_URI"])) {
        l("REQUEST URI: " . $_SERVER["REQUEST_URI"]);
        $uri = normalize_uri($_SERVER["REQUEST_URI"]);
      }
      
      $request = new Sabel_Request_Object($uri);
      
      if (SBL_SECURE_MODE) {
        $_GET  = remove_nullbyte($_GET);
        $_POST = remove_nullbyte($_POST);
      }
      
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
    
    if ($request->getHttpHeader("X-Requested-With") === "XMLHttpRequest") {
      $bus->set("NO_LAYOUT", true);
      $bus->set("IS_AJAX_REQUEST", true);
    }
  }
}
