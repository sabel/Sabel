<?php

/**
 * Flow_Processor
 *
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Flow_Processor extends Sabel_Bus_Processor
{
  private $lifetime   = null;
  private $storage    = null;
  private $action     = "";
  private $isTransit  = false;
  private $annotation = null;
  private $state      = null;
  
  protected function createStorage($namespace)
  {
    $config = array("namespace" => $namespace);
    return new Sabel_Storage_Database($config);
  }
  
  public function execute($bus)
  {
    $this->extract("session", "response", "controller", "request");
    
    $controller   = $this->controller;
    $destination  = $bus->get("destination");
    $this->action = $destination->getAction();
    
    if (!$controller->hasMethod($this->action)) return;
    
    $annotation = $controller->getReflection()
                             ->getMethod($this->action)
                             ->getAnnotation("flow");
    
    if ($annotation === null || !isset($annotation[0][0])) {
      return;
    } else {
      $this->annotation = $annotation;
    }
    
    $token = $this->request->getValueWithMethod("token");
    $this->state = $state = $this->getFlowState($token, $destination);
    if (!$state) return;
    
    $controller->setAttribute("flow",  $state);
    $controller->setAttribute("token", $state->token);
    
    if ($this->isStartAction()) {
      l("[flow] start flow state with: '{$state->token}'", SBL_LOG_DEBUG);
      
      $state->setCurrentActivity($this->action);
      
      if ($nexts = $this->getNextActions()) {
        $state->setNextActions($nexts);
        $this->isTransit = true;
      } else {
        $message = "no next actions.";
        throw new Sabel_Exception_Runtime($message);
      }
    } else {
      l("[flow] restore flow state with: '{$state->token}'", SBL_LOG_DEBUG);
      
      $this->executeInFlowAction();
      
      $vars = $this->getContinuationVariables();
      foreach ($vars as $var) {
        $controller->$var = $state->$var;
      }
    }
  }
  
  public function shutdown($bus)
  {
    if ($this->isTransit && !$bus->get("response")->isFailure()) {
      $state = $this->state;
      $controller = $this->controller;
      $vars = $this->getContinuationVariables();
      
      foreach ($vars as $var) {
        $state->$var = $controller->$var;
      }
      
      $state->save($this->storage, $this->lifetime);
    }
  }
  
  private function isStartAction()
  {
    $bool = false;
    foreach ($this->annotation as $annot) {
      if ($annot[0] === "start") {
        $bool = true;
        break;
      }
    }
    
    return ($bool || $this->isOnce());
  }
  
  private function isEndAction()
  {
    foreach ($this->annotation as $annot) {
      if ($annot[0] === "end") {
        return true;
      }
    }
    
    return false;
  }
  
  private function isOnce()
  {
    foreach ($this->annotation as $annot) {
      if ($annot[0] === "once") {
        return true;
      }
    }
    
    return false;
  }
  
  private function getNextActions()
  {
    foreach ($this->annotation as $annot) {
      if ($annot[0] === "next" && isset($annot[1])) {
        unset($annot[0]);
        return $annot;
      }
    }
    
    return null;
  }
  
  private function getFlowState($token, $destination)
  {
    $namespace = $this->session->getClientId()
               . $destination->getModule()
               . $destination->getController();
    
    $this->storage = $this->createStorage(md5($namespace));
    
    if ($this->isStartAction()) {
      $state = new Flow_State(md5hash());
    } elseif ($token === null) {
      l("[flow] token is null", SBL_LOG_DEBUG);
      $this->response->getStatus()->setCode(Sabel_Response::BAD_REQUEST);
      return false;
    } else {
      if ($data = $this->storage->fetch($token)) {
        $state = new Flow_State($token);
        $state->restore($data);
      } else {
        l("[flow] invalid token", SBL_LOG_DEBUG);
        $this->response->getStatus()->setCode(Sabel_Response::BAD_REQUEST);
        return false;
      }
    }
    
    return $state;
  }
  
  private function executeInFlowAction()
  {
    $state = $this->state;
    $currentActivity = $state->getCurrentActivity();
    if ($this->action === $currentActivity) {
      $this->isTransit = $this->request->isPost();
      return;
    }
    
    if ($state->isMatchToNext($this->action)) {
      if ($this->isEndAction()) {
        $this->lifetime = 60;
      } elseif ($nexts = $this->getNextActions()) {
        $state->setNextActions($nexts);
      } else {
        $message = __METHOD__ . "() no next actions.";
        throw new Sabel_Exception_Runtime($message);
      }
      
      $state->warning = null;
      $state->transit($this->action);
      $this->isTransit = true;
    } else {
      if ($state->isPreviousAction($this->action)) {
        $message = "It is possible to move to the previous page "
                 . "with browser's back button.";
        
        $state->warning = $message;
      }
      
      l("[flow] invalid sequence. redirect...", SBL_LOG_DEBUG);
      $this->controller->getRedirector()->to("a: " . $currentActivity);
    }
  }
  
  private function getContinuationVariables()
  {
    $annotations = $this->controller->getReflection()->getAnnotation("flow");
    if ($annotations === null) return array();
    
    foreach ($annotations as $annotation) {
      if ($annotation[0] === "continuation") {
        unset($annotation[0]);
        return array_values($annotation);
      }
    }
    
    return array();
  }
}
