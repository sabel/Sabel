<?php

/**
 * Processor_Response
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Response extends Sabel_Bus_Processor
{
  protected $afterEvents = array("action" => "afterAction");
  
  public function execute($bus)
  {
    $bus->set("response", new Sabel_Response_Object());
  }
  
  public function afterAction($bus)
  {
    $response = $bus->get("response");
    $response->setResponses(array_merge(
      $response->getResponses(),
      $bus->get("controller")->getAttributes()
    ));
    
    if ($response->getStatus()->isServerError()) {
      $exception = Sabel_Context::getContext()->getException();
      if (!is_object($exception)) return;
      
      $eol = ((ENVIRONMENT & DEVELOPMENT) > 0) ? "<br />" : PHP_EOL;
      $msg = get_class($exception) . ": "
           . $exception->getMessage()  . $eol
           . "At: " . date("r") . $eol . $eol
           . Sabel_Exception_Printer::printTrace($exception, $eol, true);
      
      if ((ENVIRONMENT & PRODUCTION) > 0) {
        
      } else {
        $response->setResponse("exception_message", $msg);
      }
      
      l(PHP_EOL . str_replace("<br />", PHP_EOL, $msg), SBL_LOG_ERR);
    }
  }
  
  public function shutdown($bus)
  {
    $response = $bus->get("response");
    $redirector = $response->getRedirector();
    
    if ($redirector->isRedirected()) {
      if (($url = $redirector->getUrl()) !== "") {
        $response->setLocation($url);
      } else {
        $token     = $bus->get("request")->getValueWithMethod("token");
        $hasToken  = !empty($token);
        $hasParams = $redirector->hasParameters();
        $location  = $redirector->getUri();
        
        if ($hasToken) {
          $glue = ($hasParams) ? "&" : "?";
          $location .= $glue . "token={$token}";
        }
        
        $session = $bus->get("session");
        if ($session->isStarted() && !$session->isCookieEnabled()) {
          $glue = ($hasToken || $hasParams) ? "&" : "?";
          $location .= $glue . $session->getName() . "=" . $session->getId();
        }
        
        if (function_exists("get_uri_prefix")) {
          $location = get_uri_prefix() . "/" . ltrim($location, "/");
        }
        
        $response->setLocation($location);
      }
    }
    
    $response->outputHeader();
  }
}
