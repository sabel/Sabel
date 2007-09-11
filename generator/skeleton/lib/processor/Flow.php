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
    
    $name = $this->controller->getName();
    $this->reflection = new Sabel_Annotation_ReflectionClass($name);
    
    $state = new Processor_Flow_State($this->storage);
    $token = $this->request->getValueWithMethod("token");
    
    l("[flow] token is " . $token);
    
    if ($token !== null) {
      $state = $state->restore($token);
    }
    
    if ($state->isInFlow()) {
      $method = $this->reflection->getMethod($this->action);
      
      $this->controller->setAttribute("flow", $state);
      $this->controller->setAttribute("token", $token);
      
      if ($method->hasAnnotation("end")) {  
        $this->executeAction($bus);
        $state->end($token);
      } else {
        // 状態中の通常アクション
        $annot = $method->getAnnotation("next");
        $nextAction = $annot[0][0];
        
        if ($state->isMatchToNext($this->action)) {
          $this->controller->setAttribute("flow", $state);

          $response = $this->executeAction($bus);
          
          $method = $this->reflection->getMethod($this->action);
          $next = $method->getAnnotation("next");
          $state->setNextActions($next[0]);
          $state->transit($this->action);
          $state->save();
        } else {
          l("[flow] invalid sequence");
          $this->controller->setAction($this->action);
          $this->controller->initialize();
          
          $injector = Sabel_Container::injector(new Factory());
          
          $state->setNextActions(array($state->getCurrent()));
          $this->controller->redirect->to("a: " . $state->getCurrent());
          $response = $injector->newInstance("Sabel_Response");
          $response->forbidden();
          
          $bus->set("response", $response);
          
          return true;
        }
      }
    } else {
      // start flow state
      $startAction = $this->reflection->getAnnotation("flow");
      
      if ($startAction[0][1] === $this->action) {
        $token = $this->createToken();
        $state->start($this->action, $token);
        
        $method = $this->reflection->getMethod($this->action);
        $next = $method->getAnnotation("next");
        $state->setNextActions($next[0]);
        l("[flow] start state with " . $token);

        $this->controller->setAttribute("flow", $state);
        $this->controller->setAttribute("token", $token);

        $this->executeAction($bus);
        $state->save();
      } else {
        echo "[flow] your request was denied";exit;
      }
    }
    
    output_add_rewrite_var("token", $token);
    
    return true;
  }
  
  private final function executeAction($bus)
  {
    $this->controller->setAction($this->action);
    $this->controller->initialize();
    
    $response = $this->controller->execute($this->action);
    $bus->set("response", $response);
    
    return $response;
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
  
  private function createToken()
  {
    $token = "";
    
    $token  = substr(sha1(uniqid(microtime().mt_rand(), true)), 0, 5);
    $token .= substr(sha1(uniqid(microtime().mt_rand(), true)), 35, 40);
    
    return $token;
  }
}
