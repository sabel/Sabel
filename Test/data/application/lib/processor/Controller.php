<?php

class TestProcessor_Controller extends Sabel_Bus_Processor
{
  const CONTROLLERS_DIR = "controllers";
  
  public function execute($bus)
  {
    $destination = $bus->get("destination");
    $redirector  = new Sabel_Controller_Redirector();
    $response    = new Sabel_Response_Object();
    
    if (($controller = $this->createController($response, $destination)) === null) {
      $controller = $this->createDefaultController($response);
    }
    
    $controller->setRedirector(new Sabel_Controller_Redirector());
    
    if (($request = $bus->get("request")) !== null) {
      $controller->setRequest($request);
    }
    
    if (($storage = $bus->get("storage")) !== null) {
      $controller->setStorage($storage);
    }
    
    $bus->set("response",   $response);
    $bus->set("controller", $controller);
  }
  
  protected function createController($response, $destination)
  {
    list ($module, $controller,) = $destination->toArray();
    $class = ucfirst($module) . "_" . ucfirst(self::CONTROLLERS_DIR) . "_" . ucfirst($controller);
    
    Sabel::using($class);
    
    if (class_exists($class, false)) {
      l("create controller '{$class}'");
      return new $class($response);
    } else {
      return null;
    }
  }
  
  protected function createDefaultController($response)
  {
    $class = "Index_" . ucfirst(self::CONTROLLERS_DIR) . "_Index";
    Sabel::using($class);
    
    if (class_exists($class, false)) {
      l("create default controller '{$class}'");
      return new $class($response);
    } else {
      throw new Sabel_Exception_Runtime("default controller not found.");
    }
  }
  
  public function shutdown($bus)
  {
    $controller = $bus->get("controller");
    
    if ($controller->isRedirected()) {
      if (defined("URI_IGNORE")) {
        $ignored = ltrim(Sabel_Environment::get("SCRIPT_NAME"), "/") . "/";
      } else {
        $ignored = "";
      }
      
      $token = $controller->getRequest()->getToken()->getValue();
      $redirector = $controller->getRedirector();
      
      if (empty($token)) {
        $to = $redirector->getUrl();
      } elseif ($redirector->hasParameters()) {
        $to = $redirector->getUrl() . "&token={$token}";
      } else {
        $to = $redirector->getUrl() . "?token={$token}";
      }
      
      $serverName = Sabel_Environment::get("SERVER_NAME");
      $bus->get("response")->location($serverName, $ignored . $to);
    }
  }
}
