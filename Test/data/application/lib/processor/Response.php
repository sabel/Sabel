<?php

class TestProcessor_Response extends Sabel_Bus_Processor
{
  protected $afterEvents = array("executer" => "afterAction");
  
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
        $session   = $bus->get("session");
        $token     = $bus->get("request")->getValueWithMethod("token");
        $hasToken  = !empty($token);
        $hasParams = $redirector->hasParameters();
        
        if (!$hasToken) {
          $to = $redirector->getUri();
        } elseif ($hasParams) {
          $to = $redirector->getUri() . "&token={$token}";
        } else {
          $to = $redirector->getUri() . "?token={$token}";
        }
        
        if ($session->isStarted() && !$session->isCookieEnabled()) {
          $glue = ($hasToken || $hasParams) ? "&" : "?";
          $to  .= $glue . $session->getName() . "=" . $session->getId();
        }
        
        $response->setLocation($to, $_SERVER["SERVER_NAME"]);
      }
    }
    
    $response->outputHeader();
  }
}
