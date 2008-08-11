<?php

class TestProcessor_Executer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $status     = $bus->get("response")->getStatus();
    $redirector = $bus->get("redirector");
    $controller = $bus->get("controller");
    
    if ($status->isFailure() || $redirector->isRedirected()) return;
    
    $action    = $bus->get("destination")->getAction();
    $hasAction = $controller->hasMethod($action);
    $request   = $bus->get("request");
    
    try {
      $controller->setAction($action);
      $controller->initialize();
      
      if ($status->isFailure() || $redirector->isRedirected()) return;
      
      if ($hasAction) {
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $status->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
      Sabel_Context::getContext()->setException($e);
    }
    
    if ($controller->getAttribute("layout") === false) {
      $bus->set("noLayout", true);
    }
  }
}
