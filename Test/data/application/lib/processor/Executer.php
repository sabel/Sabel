<?php

class TestProcessor_Executer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response   = $bus->get("response");
    $controller = $bus->get("controller");
    
    if ($response->isFailure() || $controller->isRedirected()) return;
    
    try {
      $action = $bus->get("destination")->getAction();
      $controller->setAction($action);
      $controller->initialize();
      
      if (!$response->isFailure() && !$controller->isRedirected()) {
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $response->serverError();
      Sabel_Context::getContext()->setException($e);
    }
  }
}
