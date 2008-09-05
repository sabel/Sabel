<?php

class TestProcessor_Executer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response   = $bus->get("response");
    $status     = $response->getStatus();
    $controller = $bus->get("controller");
    
    if ($response->isFailure() || $response->isRedirected()) return;
    
    $action = $bus->get("destination")->getAction();
    $controller->setAction($action);
    
    try {
      $controller->initialize();
      
      if ($response->isFailure() || $response->isRedirected()) return;
      
      $controller->execute();
    } catch (Exception $e) {
      $status->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
      Sabel_Context::getContext()->setException($e);
    }
    
    if ($controller->getAttribute("layout") === false) {
      $bus->set("noLayout", true);
    }
  }
}
