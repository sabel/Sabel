<?php

/**
 * Flow_Processor
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_Processor extends Sabel_Bus_Processor
{
  private $action    = "";
  private $isTransit = false;
  private $refMethod = null;
  private $state     = null;
  
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
    
    if (!$this->controller instanceof Flow_Page) return;
    
    $controller = $this->controller;
    $response = $this->response;
    
    $key = implode("_", array($this->destination->getModule(),
                              $this->destination->getController()));
                              
    $this->state = $state = new Flow_State($this->storage);
    $token = $this->request->getToken()->getValue();
    
    l("[flow] token is '{$token}'");
    
    if (!$controller->hasMethod($this->action)) {
      $bus->getList()->find("executer")->unlink();
      return $response->notFound();
    }
    
    $this->refMethod = $controller->getReflection()->getMethod($this->action);
    
    if ($token !== null && !$this->isStartAction()) {
      $state = $state->restore($key, $token);
    }
    
    if ($this->isIgnoreAction()) return;
    
    if ($state === null) {
      l("[flow] invalid token '{$token}'.");
      $bus->getList()->find("executer")->unlink();
      return $response->notFound();
    }
    
    if ($state->isInFlow() && !$this->isStartAction()) {
      $controller->setAttribute("flow",  $state);
      $controller->setAttribute("token", $token);
      
      if ($this->refMethod->hasAnnotation("end")) {
        $this->transit(false);
        $state->end();
      } else {
        $this->executeInFlowAction($state);
        $state->clearEndFlow();
      }
      
      if (($warning = $state->warning) !== null) {
        // @todo
        $response->setResponse("errmsg", $warning);
      }
      
      foreach ($state->getProperties() as $name => $val) {
        $response->setResponse($name, $val);
      }
    } elseif ($this->isStartAction()) {
      $token = $this->request->getToken()->createValue();
      $state->start($key, $this->action, $token);
      $state->clearEndFlow();
      
      l("[flow] start state with " . $token);
      
      if (($endAction = $this->isOnce()) === false) {
        $next = $this->refMethod->getAnnotation("next");
        $state->setNextActions($next[0]);
      } else {
        $state->setNextActions(array($endAction));
      }
      
      $controller->setAttribute("flow", $state);
      $controller->setAttribute("token", $token);
      $this->transit(true);
      
      foreach ($state->getProperties() as $name => $val) {
        $response->setResponse($name, $val);
      }
    } else {
      l("[flow] your request was denied.");
      $bus->getList()->find("executer")->unlink();
      return $response->notFound();
    }
    
    ini_set("url_rewriter.tags", "input=src,fieldset=");
    output_add_rewrite_var("token", $token);
  }
  
  public function afterExecute($bus)
  {
    if ($this->isTransit() && $this->response->isSuccess()) {
      $this->state->save();
    }
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
  
  private function executeInFlowAction($state)
  {
    $controller = $this->controller;
    if ($this->action === $state->getCurrent()) {
      $this->transit(false);
    } elseif ($state->isMatchToNext($this->action)) {
      $next = $this->refMethod->getAnnotation("next");
      $state->setNextActions($next[0]);
      $state->transit($this->action);
      $state->warning = null;
      $this->transit(true);
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
  
  private function transit($bool)
  {
    $this->isTransit = $bool;
  }
  
  private function isTransit()
  {
    return $this->isTransit;
  }
}
