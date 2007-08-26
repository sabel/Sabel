<?php

class Processor_Flow extends Sabel_Bus_Processor
{
  private $reflection = null;
  
  private $request = null;
  private $storage = null;
  private $controller  = null;
  private $destination = null;
  
  private $action = "";
    
  public function execute($bus)
  {
    $this->request     = $bus->get("request");
    $this->storage     = $bus->get("storage");
    $this->controller  = $bus->get("controller");
    $this->destination = $bus->get("destination");
    $this->action = $this->destination->getAction();
    
    $className = get_class($this->controller);
    $this->reflection = new Sabel_Annotation_ReflectionClass($className);
    
    $method = $this->reflection->getMethod($this->action);
    $methodAnnot = $method->getAnnotation("flow");
    
    $state = new Processor_Flow_State($this->storage);
    $token = $this->request->getValueWithMethod("token");
    
    if ($token !== null) {
      $state = $state->restore($token);
    }
    
    if ($state->isInFlow()) {
      
    } else {
      if ($methodAnnot === "start") {
        // here is entry activity
        $state->start($this->action, $this->createToken());
      }
    }
    $this->controller->setAttribute("flow", $state);
    
    $this->executeAction($bus);
    
    return true;
    
    /*
    return true;
    
    if ($flow->isInFlow()) {
      $this->processFlow();
    } else {
      $result = $this->startFlow();
    }
    */
  }
  
  private final function executeAction($bus)
  {
    $this->controller->setAction($this->action);
    $this->controller->initialize();
    
    $response = $this->controller->execute($this->action);
    $bus->set("response", $response);
  }
  
  public function startFlow()
  {
    if ($this->isEntryActivity()) {
      l("{$action} is entry activity");
      $flow->start($action);
      $this->assignToken($manager, $controller, $flow);
      $response = $controller->execute($action);
      $manager->save($flow);
      return $response;
    } elseif ($flow->isEndActivity($action)) {
      $response = $controller->execute($action);
      $manager->remove();
      return $response;
    } elseif (!$flow->isActivity($action)) {
      return $controller->execute($action);
    } else {
      $this->destination->setAction(self::INVALID_ACTION);
      return $controller->execute(self::INVALID_ACTION);
    }
  }
  
  private final function isEntryActivity()
  {
    
  }
  
  public function processFlow()
  {
    if (!$this->isActivity($action)) {
      return $controller->execute($action);
    }
      
    if ($flow->canTransitTo($action)) {
      $response = $controller->execute($action);
              
      if ($response->result === null) {
        $response->result = true;
      }
      
      if ($response->result) {
        $nextAction = $flow->transit($action);
        $controller->redirectTo("a: " . $nextAction->getName());
      } else {
        $controller->redirectTo("a: " . $flow->getCurrentActivity()->getName());
      }
      
      $manager->save($flow);
      $this->assignToken($manager, $controller, $flow);
      
      return $controller->getResponse();
    } elseif ($flow->isCurrent($action)) {
      $manager->save($flow);
      $this->assignToken($manager, $controller, $flow);
      return $controller->execute($action);
    } else {
      $manager->save($flow);
      $this->assignToken($manager, $controller, $flow);
      $this->destination->setAction(self::INVALID_ACTION);
      return $controller->execute(self::INVALID_ACTION);
    }
  }
  
  private function createToken()
  {
    $token = "";
    
    $token  = substr(sha1(uniqid(microtime().mt_rand(), true)), 0, 5);
    $token .= substr(sha1(uniqid(microtime().mt_rand(), true)), 35, 40);
    
    return $token;
  }
}
