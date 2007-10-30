<?php

class Processor_Flow extends Sabel_Bus_Processor
{
  private $action = "";
  
  private function initialize($bus)
  {
    $require = array("request", "storage", "controller", "destination");
    
    if ($bus->has($require)) {
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
    
    $state = new Processor_Flow_State($this->storage);
    $token = $this->request->getValueWithMethod("token");
    
    l("[flow] token is " . $token);
    
    if (!$this->controller->hasMethod($this->action)) {
      $this->response = $this->controller->execute($this->action)->getResponse();
      return;
    }
    
    if ($token !== null && !$this->isStartAction()) {
      $state = $state->restore($key, $token);
    }
    
    $method = $this->controller->getReflection()->getMethod($this->action);
    $methodAnnotation = $method->getAnnotation("flow");
    if ($methodAnnotation[0][0] === "ignore") {
      $this->executeAction($bus);
      return true;
    }
    
    if ($state === null) {
      $this->destination->setAction("notFound");
      return;
    }
    
    if ($state->isInFlow() && !$this->isStartAction()) {
      $this->controller->setAttribute("flow",  $state);
      $this->controller->setAttribute("token", $token);
      
      if (!$method->hasAnnotation("end")) {
        $this->executeInFlowAction($method, $state, $bus);
      } else {
        $this->executeAction($bus);
        $state->end($token);
      }
      
      foreach ($state->getProperties() as $name => $val) {
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
          $method = $this->controller->getReflection()->getMethod($this->action);
          $next = $method->getAnnotation("next");
          $state->setNextActions($next[0]);
        }
        
        $this->controller->setAttribute("flow", $state);
        $this->controller->setAttribute("token", $token);
        $this->executeAction($bus);
        $state->save();
        
        foreach ($state->getProperties() as $name => $val) {
          $this->controller->getResponse()->setResponse($name, $val);
        }
      } else {
        l("[flow] your request was denied");
        $this->response = $this->controller->getResponse()->notFound();
        return;
      }
    }
    
    ini_set("url_rewriter.tags", "input=src,fieldset=");
    output_add_rewrite_var("token", $token);
  }
  
  private function isStartAction()
  {
    $method = $this->controller->getReflection()->getMethod($this->action);
    
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
    $method = $this->controller->getReflection()->getMethod($this->action);
    
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
      
      $injector = Sabel_Container::injector(new Config_Factory());
      
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
    try {
      $this->controller->setAction($this->action);
      $this->controller->initialize();
      
      $this->response = $this->controller->execute($this->action)->getResponse();
      if ($this->response->isNotFound()) {
        $this->destination->setAction("notFound");
        return false;
      }
      
      return $this->response;
    } catch (Exception $e) {
      l($e->getMessage());
      $this->response->serverError();
      if (ENVIRONMENT === PRODUCTION) {
        $this->destination->setAction("invalid");
      } else {
        $this->destination->setAction("serverError");
      }
      
      Sabel_Context::getContext()->setException($e);
    }
  }
  
  private function createToken()
  {
    return md5(uniqid(microtime() . mt_rand(), true));
  }
}
