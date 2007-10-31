<?php

class Flow_Redirecter extends Sabel_Bus_Processor
{
  public function execute($bus)
  {    
    $redirect = new Processor_Redirecter_Redirect($bus);
    $this->controller->setAttribute("redirect", $redirect);
    
    return new Sabel_Bus_ProcessorCallback($this, "onRedirect", "executer");
  }
  
  public function onRedirect($bus)
  {
    $redirect = $this->controller->getAttribute("redirect");
    
    if ($redirect->isRedirected()) {
      if (isset($_SERVER["HTTP_HOST"])) {
        $host = $_SERVER["HTTP_HOST"];
      } else {
        $host = "localhost";
      }
      
      $ignored = "";
      
      if (defined("URI_IGNORE")) {
        $ignored = ltrim($_SERVER["SCRIPT_NAME"], "/") . "/";
      }
      
      $token = $this->controller->getAttribute("token");
      if ($redirect->hasParameters()) {
        $to = $redirect->getUrl() . "&token={$token}";
      } else {
        $to = $redirect->getUrl() . "?token={$token}";
      }
      
      $this->response->location($host, $ignored . $to);
    }
  }
}