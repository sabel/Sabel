<?php

class Flow_Processor extends Sabel_Bus_Processor
{
  private $action = "";
  private $refMethod = null;
  
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
    $controller = $this->controller;
    
    $key = implode("_", array($this->destination->getModule(),
                              $this->destination->getController()));
                              
    $state = new Flow_State($this->storage);
    $token = $this->request->getToken()->getValue();
    
    l("[flow] token is '{$token}'");
    
    if (!$controller->hasMethod($this->action)) return;
    $this->refMethod = $controller->getReflection()->getMethod($this->action);
    
    if ($token !== null && !$this->isStartAction()) {
      $state = $state->restore($key, $token);
    }
    
    if ($this->isIgnoreAction()) {
      return $this->executeAction($bus);
    } elseif ($state === null) {
      l("[flow] invalid token '{$token}'.");
      return $this->destination->setAction("notFound");
    } elseif ($state->isInFlow() && !$this->isStartAction()) {
      $controller->setAttribute("flow",  $state);
      $controller->setAttribute("token", $token);
      
      if ($this->refMethod->hasAnnotation("end")) {
        $this->executeAction($bus);
        $state->end();
      } else {
        $this->executeInFlowAction($state, $bus);
        $state->clearEndFlow();
      }
      
      $response = $controller->getResponse();
      if (($warning = $state->warning) !== null) {
        $response->setResponse("_message_", $warning);
      }
      
      foreach ($state->getProperties() as $name => $val) {
        $response->setResponse($name, $val);
      }
    } elseif ($this->isStartAction()) { // start flow state.
      $token = $this->request->getToken()->createValue();
      $state->start($key, $this->action, $token);
      $state->clearEndFlow();
      
      l("[flow] start state with " . $token);
      
      if (($endAction = $this->isOnce()) === false) {
        $method = $controller->getReflection()->getMethod($this->action);
        $next = $method->getAnnotation("next");
        $state->setNextActions($next[0]);
      } else {
        $state->setNextActions(array($endAction));
      }
      
      $controller->setAttribute("flow", $state);
      $controller->setAttribute("token", $token);
      $this->executeAction($bus);
      $state->save();
      
      foreach ($state->getProperties() as $name => $val) {
        $controller->getResponse()->setResponse($name, $val);
      }
    } else {
      l("[flow] your request was denied");
      return $controller->getResponse()->notFound();
    }
    
    ini_set("url_rewriter.tags", "input=src,fieldset=");
    output_add_rewrite_var("token", $token);
  }
  
  private function isStartAction()
  {
    $annot = $this->refMethod->getAnnotation("flow");
    if (!isset($annot[0][0])) return false;
    
    return ($annot[0][0] === "start" || $this->isOnce());
  }
  
  private function isIgnoreAction()
  {
    $annot = $this->refMethod->getAnnotation("flow");
    return (isset($annot[0][0]) && $annot[0][0] === "ignore");
  }
  
  private function isOnce()
  {
    $annot = $this->refMethod->getAnnotation("flow");
    if (!isset($annot[0][0])) return false;
    
    return ($annot[0][0] === "once") ? $annot[0][1] : false;
  }
  
  private function executeInFlowAction($state, $bus)
  {
    $controller = $this->controller;
    if ($this->action === $state->getCurrent()) {
      $controller->setAttribute("flow", $state);
      $this->executeAction($bus);
    } elseif ($state->isMatchToNext($this->action)) {
      $controller->setAttribute("flow", $state);
      $this->executeAction($bus);
      $next = $this->refMethod->getAnnotation("next");
      $state->setNextActions($next[0]);
      $state->transit($this->action);
      $state->warning = null;
      $state->save();
    } else {
      if ($state->isPreviousAction($this->action)) {
        $message = "It is possible to move to the previous page "
                 . "with browser's back button.";
                 
        $state->warning = $message;
      } else {
        l("[flow] invalid sequence.");
      }
      
      $controller->redirect->to("a: " . $state->getCurrent());
    }
  }
  
  private final function executeAction($bus)
  {
    try {
      $this->controller->setAction($this->action);
      $this->controller->initialize();
      $this->controller->execute($this->action)->getResponse();
    } catch (Exception $e) {
      l($e->getMessage());
      $this->response->serverError();
      $this->destination->setAction("serverError");
      Sabel_Context::getContext()->setException($e);
    }
  }
}
