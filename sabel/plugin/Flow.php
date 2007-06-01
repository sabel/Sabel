<?php

/**
 * Sabel_Controller_Executer_Flow
 *
 * @category   Controller
 * @package    org.sabel.controller.executer
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Plugin_Flow extends Sabel_Plugin_Base
{
  const INVALID_ACTION = "invalid";
  
  public function enable()
  {
    return array(parent::ON_EXECUTE_ACTION);
  }
  
  /**
   * execute an action.
   * overwrite parent executeAction method.
   *
   * @param string $action
   */
  public function onExecuteAction($action)
  {
    $logger = Sabel_Context::getLogger();
    
    $controller = $this->controller;
    $action     = $controller->getAction();
    
    $manager = new Sabel_Plugin_Flow_Manager($controller->getRequest());
    
    if (!($flow = $manager->restore())) {
      $dest = $this->destination->toArray();
      list($m, $c,) = array_map("ucfirst", $dest);

      $flowClass = $m."_Flow_".$c;
      if (class_exists($flowClass)) {
        $flow = new $flowClass();
        $flow->configure();
      } else {
        return $controller->execute($action);
      }
    }
    
    $controller->flow = $flow;
    
    if ($flow->isInFlow()) {
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
    } else {
      if ($flow->isEntryActivity($action)) {
        $logger->log("{$action} is entry activity");
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
  }
  
  private final function assignToken($manager, $controller, $flow)
  {
    $token = $manager->getToken();
    $controller->token = $token;
    Sabel_View::assign("token", $token);
    Sabel_View::assignByArray($flow->toArray());
  }
}
