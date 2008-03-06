<?php

class TestProcessor_Response extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response  = $bus->get("response");
    $responses = array_merge($response->getResponses(),
                             $bus->get("controller")->getAttributes());
    
    $response->setResponses($responses);
    
    if ($response->isServerError()) {
      $exception = Sabel_Context::getContext()->getException();
      if (!is_object($exception)) return;
      
      $eol = (ENVIRONMENT === DEVELOPMENT) ? "<br/>" : PHP_EOL;
      $msg = "Exception: (" . get_class($exception) . ") "
           . $exception->getMessage()  . $eol
           . "At: " . date("r") . $eol . $eol
           . Sabel_Exception_Printer::printTrace($exception, $eol, true);
      
      if (ENVIRONMENT === PRODUCTION) {
        
      } else {
        $response->setResponse("exception_message", $msg);
      }
      
      l(preg_replace('/<br ?\/?>/', PHP_EOL, $msg), SBL_LOG_ERR);
    }
  }
  
  public function shutdown($bus)
  {
    $bus->get("response")->outputHeader();
  }
}
