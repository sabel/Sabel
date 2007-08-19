<?php

class Sabel_Processor_Flow extends Sabel_Bus_Processor
{
  private
    $bus         = null,
    $controller  = null,
    $destination = null,
    $request     = null,
    $storage     = null;
  
  public function execute($bus)
  {
    $this->bus = $bus;
    
    $this->controller  = $bus->get("controller");
    $this->destination = $bus->get("destination");
    $this->request     = $bus->get("request");
    $this->storage     = $bus->get("storage");
    
    $action = $this->destination->getAction();
    
    $this->controller->setup($this->request, $this->destination, $this->storage);
    $this->controller->setAction($action);
    $this->controller->initialize();
    
    $manager = new Sabel_Plugin_Flow_Manager($this->request, $this->storage);
    
    if (!($flow = $manager->restore())) {
      $dest = $this->destination->toArray();
      list($m, $c,) = array_map("ucfirst", $dest);

      $flowClass = $m . "_Flow_" . $c;
      if (class_exists($flowClass)) {
        $flow = new $flowClass();
        $flow->configure();
      } else {
        $response = $this->controller->execute($action);
        return $bus->set("response", $response);
      }
    }
    
    $this->controller->setAttribute("flow", $flow);
    
    if ($flow->isInFlow()) {
      $this->executeInFlow();
    } else {
      $this->executeNotinFlow();
    }
  }
  
  private function executeInFlow()
  {
    $action = $this->destination->getAction();
    
    if (!$flow->isActivity($action)) {
      $response = $this->controller->execute($action);
      $this->bus->set("response", $response);
    }
      
    if ($flow->canTransitTo($action)) {
      $response = $this->controller->execute($action);
              
      if ($response->result === null) {
        $response->result = true;
      }
      
      if ($response->result) {
        $nextAction = $flow->transit($action);
        $this->controller->redirect->to("a: " . $nextAction->getName());
      } else {
        $this->controller->redirect->to("a: " . $flow->getCurrentActivity()->getName());
      }
      
      $manager->save($flow);
      $this->assignToken($manager, $flow);
      
      $this->bus->set("response", $response);
    } elseif ($flow->isCurrent($action)) {
      $manager->save($flow);
      $this->assignToken($manager, $flow);
      $response = $this->controller->execute($action);
      $this->bus->set("response", $response);
    } else {
      $manager->save($flow);
      $this->assignToken($manager, $flow);
      $this->destination->setAction(self::INVALID_ACTION);
      
      $response = $this->controller->execute(self::INVALID_ACTION);
      $this->bus->set("response", $response);
    }
  }
  
  private function executeNotinFlow()
  {
    $action = $this->destination->getAction();
    
    if ($flow->isEntryActivity($action)) {
      l("{$action} is entry activity");
      $flow->start($action);
      $this->assignToken($manager, $flow);
      $response = $this->controller->execute($action);
      $manager->save($flow);
      $this->bus->set("response", $response);
    } elseif ($flow->isEndActivity($action)) {
      $response = $this->controller->execute($action);
      $this->bus->set("response", $response);
      $manager->remove();
      return $response;
    } elseif (!$flow->isActivity($action)) {
      $response = $this->controller->execute($action);
      $this->bus->set("response", $response);
    } else {
      $this->destination->setAction(self::INVALID_ACTION);
      $response = $this->controller->execute(self::INVALID_ACTION);
      $this->bus->set("response", $response);
    }
  }
  
  private final function assignToken($manager, $flow)
  {
    $token = $manager->getToken();
    $this->controller->setAttribute("token", $token);
    Sabel_View::assign("token", $token);
    Sabel_View::assignByArray($flow->toArray());
  }
}