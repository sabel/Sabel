<?php

/**
 * Flow_Processor
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_Processor extends Sabel_Bus_Processor
{
  const END_FLOW_SESKEY = "sbl_end_flows";
  
  private $storage   = null;
  private $action    = "";
  private $isTransit = false;
  private $refMethod = null;
  private $state     = null;
  
  public function execute($bus)
  {
    $response    = $bus->get("response");
    $controller  = $bus->get("controller");
    
    if (!$controller instanceof Flow_Page || $response->isFailure()) return;
    
    $destination  = $bus->get("destination");
    $this->action = $action = $destination->getAction();
    
    if (!$controller->hasMethod($action)) {
      return $response->notFound();
    }
    
    $this->storage = $bus->get("storage");
    $request = $bus->get("request");
    $this->refMethod = $controller->getReflection()->getMethod($action);
    
    $token = $request->getToken()->getValue();
    $state = new Flow_State($token);
    
    l("[flow] token is '{$token}'");
    
    $key = implode("_", array($destination->getModule(),
                              $destination->getController()));
                              
    if ($token !== null && !$this->isStartAction()) {
      $state = $state->restore($this->storage, $key);
    }
    
    $this->state = $state;
    if ($this->isIgnoreAction()) return;
    
    if ($state === null) {
      l("[flow] invalid token '{$token}'.");
      return $response->notFound();
    }
    
    if ($state->isInFlow() && !$this->isStartAction()) {
      $controller->setAttribute("flow",  $state);
      $controller->setAttribute("token", $token);
      
      if ($this->refMethod->hasAnnotation("end")) {
        $this->transit(false);
        $this->addEndFlow($state);
      } else {
        $this->executeInFlowAction($state, $controller);
        $this->clearEndFlow($state);
      }
      
      if (($warning = $state->warning) !== null) {
        // @todo
        $response->setResponse("errmsg", $warning);
      }
      
      foreach ($state->getProperties() as $name => $val) {
        $response->setResponse($name, $val);
      }
    } elseif ($this->isStartAction()) {
      $token = $request->getToken()->createValue();
      $state->start($key, $this->action, $token);
      $this->clearEndFlow($state);
      
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
      return $response->notFound();
    }
    
    ini_set("url_rewriter.tags", "input=src,fieldset=");
    output_add_rewrite_var("token", $token);
  }
  
  public function afterExecute($bus)
  {
    if ($this->isTransit() && $bus->get("response")->isSuccess()) {
      $this->state->save($this->storage);
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
  
  private function executeInFlowAction($state, $controller)
  {
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
      
      $controller->getRedirector()->to("a: " . $state->getCurrent());
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
  
  public function addEndFlow($state)
  {
    if (($ends = $this->storage->read(self::END_FLOW_SESKEY)) === null) {
      $ends = array($state->getStateKey());
    } else {
      $ends[] = $state->getStateKey();
    }
    
    $this->storage->write(self::END_FLOW_SESKEY, $ends);
  }
  
  public function clearEndFlow()
  {
    $storage = $this->storage;
    $ends = $storage->delete(self::END_FLOW_SESKEY);
    if ($ends === null) return;
    
    foreach ($ends as $seskey) $storage->delete($seskey);
  }
}
