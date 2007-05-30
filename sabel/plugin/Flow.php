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
    
    $manager = new Sabel_Controller_Flow_Manager($controller->getRequest());
    
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
        $guard = $controller->execute($action);
        
        if ($guard === null) {
          $guard = true;
        }
        
        if ($guard) {
          $nextAction = $flow->transit($action);
          $controller->redirectTo($nextAction->getName());
        } else {
          $controller->redirectTo($flow->getCurrentActivity()->getName());
        }
        
      } elseif ($flow->isCurrent($action)) {
        $controller->execute($action);
      } else {
        $this->destination->setAction(self::INVALID_ACTION);
        $controller->execute(self::INVALID_ACTION);
      }
      
      $manager->save($flow);
      $this->assignToken($manager, $controller, $flow);
    } else {
      if ($flow->isEntryActivity($action)) {
        $logger->log("{$action} is entry activity");
        $flow->start($action);
        $this->assignToken($manager, $controller, $flow);
        $controller->execute($action);
        $manager->save($flow);
      } elseif ($flow->isEndActivity($action)) {
        $manager->remove();
      } elseif (!$flow->isActivity($action)) {
        $controller->execute($action);
      } else {
        $this->destination->setAction(self::INVALID_ACTION);
        $controller->execute(self::INVALID_ACTION);
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
