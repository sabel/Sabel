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
  public function execute($bus)
  {
    $destination = $bus->get("destination");
    
    list ($module, $controller,) = $destination->toArray();
    $class = ucfirst($module) . "_Controllers_" . ucfirst($controller);
    
    $controller = Sabel_Container::load("Sabel_Controller_Page", new Config_Controller($class));
    
    $response = $controller->getResponse();
    
    if ($controller instanceof SabelVirtualController) {
      $response->getStatus()->setCode(Sabel_Response::NOT_FOUND);
    }
    
    if (($request = $bus->get("request")) !== null) {
      $controller->setRequest($request);
    }
    
    if (($session = $bus->get("session")) !== null) {
      $controller->setSession($session);
    }
    
    $bus->set("response",   $response);
    $bus->set("controller", $controller);
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

class Config_Controller extends Sabel_Container_Injection
{
  private $className = "";
  
  public function __construct($className)
  {
    Sabel::using($className);
    $this->className = $className;
    
    if (!class_exists($this->className, false)) {
      $this->className = "SabelVirtualController";
      if (!class_exists($this->className, false)) {
        eval ("class {$this->className} extends Sabel_Controller_Page {}");
      }
    }
  }
  
  public function configure()
  {
    l("create controller '{$this->className}'");
    
    $this->bind("Sabel_Controller_Page")
         ->to($this->className);
          
    $this->bind("Sabel_Response")
         ->to("Sabel_Response_Object")
         ->setter("setResponse");
  }
}

