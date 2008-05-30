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
    $response    = new Sabel_Response_Object();
    
    if (($controller = $this->createController($destination)) === null) {
      $response->getStatus()->setCode(Sabel_Response::NOT_FOUND);
      $controller = $this->createVirtualController();
    }
    
    $controller->setResponse($response);
    
    if (($request = $bus->get("request")) !== null) {
      $controller->setRequest($request);
    }
    
    if (($session = $bus->get("session")) !== null) {
      $controller->setSession($session);
    }
    
    $bus->set("response",   $response);
    $bus->set("controller", $controller);
  }
  
  protected function createController($destination)
  {
    list ($module, $controller,) = $destination->toArray();
    $class = ucfirst($module) . "_" . ucfirst(self::CONTROLLERS_DIR) . "_" . ucfirst($controller);
    
    if (Sabel::using($class)) {
      l("create controller '{$class}'");
      return new $class();
    } else {
      l("controller '{$class}' not found");
      return null;
    }
  }
  
  protected function createVirtualController()
  {
    $className = "SabelVirtualController";
    
    if (!class_exists($className, false)) {
      eval ("class $className extends Sabel_Controller_Page {}");
    }
    
    l("create virtual controller '{$className}'");
    
    return new $className();
  }
  
  public function shutdown($bus)
  {
    $controller = $bus->get("controller");
    
    if ($controller->isRedirected()) {
      $redirector = $controller->getRedirector();
      $request = $controller->getRequest();
      $host = $request->getHttpHeader("host");
      
      if (($url = $redirector->getUrl()) !== "") {
        return $bus->get("response")->setLocation($url);
      }
      
      $session   = $controller->getSession();
      $token     = $request->getValueWithMethod("token");
      $hasToken  = !empty($token);
      $hasParams = $redirector->hasParameters();
      
      if (!$hasToken) {
        $to = $redirector->getUri();
      } elseif ($hasParams) {
        $to = $redirector->getUri() . "&token={$token}";
      } else {
        $to = $redirector->getUri() . "?token={$token}";
      }
      
      if (!$session->isCookieEnabled()) {
        $glue = ($hasToken || $hasParams) ? "&" : "?";
        $to  .= $glue . $session->getName() . "=" . $session->getId();
      }
      
      $ignored = "";
      if (defined("URI_IGNORE")) {
        $ignored = ltrim($_SERVER["SCRIPT_NAME"], "/") . "/";
      }
      
      $bus->get("response")->setLocation($ignored . $to, $host);
    }
  }
}
