<?php

/**
 * Processor_Controller
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Controller extends Sabel_Bus_Processor
{
  const CONTROLLERS_DIR = "controllers";
  
  public function execute($bus)
  {
    $destination = $bus->get("destination");
    $redirector  = new Sabel_Controller_Redirector();
    $response    = new Sabel_Response_Object();
    
    if (($controller = $this->createController($response, $destination)) === null) {
      $controller = $this->createVirtualController($response);
    }
    
    $controller->setRedirector(new Sabel_Controller_Redirector());
    
    if (($request = $bus->get("request")) !== null) {
      $controller->setRequest($request);
    }
    
    if (($session = $bus->get("session")) !== null) {
      $controller->setSession($session);
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
  
  protected function createVirtualController($response)
  {
    $className = "SabelVirtualController";
    
    l("create virtual controller '{$className}'");
    
    if (!class_exists($className, false)) {
      eval ("class $className extends Sabel_Controller_Page {}");
    }
    
    return new $className($response);
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
      
      $session    = $bus->get("session");
      $token      = $controller->getRequest()->getValueWithMethod("token");
      $hasToken   = !empty($token);
      $redirector = $controller->getRedirector();
      
      if (!$hasToken) {
        $to = $redirector->getUrl();
      } elseif ($redirector->hasParameters()) {
        $to = $redirector->getUrl() . "&token={$token}";
      } else {
        $to = $redirector->getUrl() . "?token={$token}";
      }
      
      if (!$session->isCookieEnabled()) {
        $glue = ($hasToken) ? "&" : "?";
        $to  .= $glue . $session->getName() . "=" . $session->getId();
      }
      
      $host = Sabel_Environment::get("HTTP_HOST");
      $bus->get("response")->location($host, $ignored . $to);
    }
  }
}
