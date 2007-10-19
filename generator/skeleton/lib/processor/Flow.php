<?php

class Processor_Flow extends Sabel_Bus_Processor
{
  private $reflection = null;
  
  private $request = null;
  private $storage = null;
  private $controller  = null;
  private $destination = null;
  
  private $action = "";
  
  private function initialize($bus)
  {
    $require = array("request", "storage", "controller", "destination");
    
    if ($bus->has($require)) {
      foreach ($require as $r) {
        $this->{$r} = $bus->get($r);
      }
      $this->action = $this->destination->getAction();
    } else {
      $msg = "must need required bus data: " . join(", ", $require);
      throw new Sabel_Exception_Runtime($msg);
    }
  }
    
  public function execute($bus)
  {
    $this->initialize($bus);
    
    $key = implode("_", array($this->destination->getModule(),
                              $this->destination->getController()));
    
    $name = $this->controller->getName();
    $this->reflection = new Sabel_Annotation_ReflectionClass($name);
    
    $state = new Processor_Flow_State($this->storage);
    $token = $this->request->getValueWithMethod("token");
    
    l("[flow] token is " . $token);
    
    if ($token !== null) {
      $state = $state->restore($key, $token);
    }
    
    if ($state === null) {
      echo "invalid token";exit;
    }
    
    $method = $this->reflection->getMethod($this->action);
    $methodAnnotation = $method->getAnnotation("flow");
    if ($methodAnnotation[0][0] === "ignore") {
      $this->executeAction($bus);
      return true;
    }
    
    if ($state->isInFlow()) {
      $this->controller->setAttribute("flow",  $state);
      $this->controller->setAttribute("token", $token);
      
      if (!$method->hasAnnotation("end")) {
        $this->executeInFlowAction($method, $state, $bus);
      } elseif ($state->isEndAction($this->action)) {
        $this->executeAction($bus);
        $state->end($token);
      } else {
        $this->executeAction($bus);
        $state->end($token);
      }
      
      foreach ($state->getProperties() as $name => $val) {
        l($name);
        $this->controller->getResponse()->setResponse($name, $val);
      }
    } else {
      // start flow state      
      if ($this->isStartAction()) {
        $token = $this->createToken();
        $state->start($key, $this->action, $token);
        
        l("[flow] start state with " . $token);
        
        if (($endAction = $this->isOnce()) !== false) {
          $state->setEndAction($endAction);
          $state->setNextActions(array($endAction));
        } else {
          $method = $this->reflection->getMethod($this->action);
          $next = $method->getAnnotation("next");
          $state->setNextActions($next[0]);
        }
        
        $this->controller->setAttribute("flow", $state);
        $this->controller->setAttribute("token", $token);
        $this->executeAction($bus);
        $state->save();
        
        foreach ($state->getProperties() as $name => $val) {
          l($name);
          $this->controller->getResponse()->setResponse($name, $val);
        }
      } else {
        echo "[flow] your request was denied";exit;
      }
    }
    
    output_add_rewrite_var("token", $token);
    
    return true;
  }
  
  private function isStartAction()
  {
    $method = $this->reflection->getMethod($this->action);
    if ($method->hasAnnotation("flow")) {
      $annot = $method->getAnnotation("flow");
      if ($annot[0][0] === "start" || $this->isOnce()) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  private function isOnce()
  {
    $method = $this->reflection->getMethod($this->action);
    if ($method->hasAnnotation("flow")) {
      $annot = $method->getAnnotation("flow");
      if ($annot[0][0] === "once") {
        return $annot[0][1];
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  private function executeInFlowAction($method, $state, $bus)
  {
    $annot = $method->getAnnotation("next");
    $nextAction = $annot[0][0];
    
    if ($this->action === $state->getCurrent()) {
      $this->controller->setAttribute("flow", $state);
      $response = $this->executeAction($bus);
    } else if ($state->isMatchToNext($this->action)) {
      $this->controller->setAttribute("flow", $state);
      $response = $this->executeAction($bus);
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
    }
    
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
  
  private function createToken()
  {
    return md5(uniqid(microtime() . mt_rand(), true));
  }
}
