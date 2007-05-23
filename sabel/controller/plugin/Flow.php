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
class Sabel_Controller_Plugin_Flow extends Sabel_Controller_Page_Plugin
{
  const INVALID_ACTION = "invalid";
  
  /**
   * execute an action.
   * overwrite parent executeAction method.
   *
   * @param string $action
   */
  public function onExecuteAction($action)
  {
    $logger = Sabel_Context::getLogger();
    
    $manager = new Sabel_Controller_Flow_Manager($this->getRequest());
    
    $controller = $this->getController();
    $action     = $controller->getAction();
    
    if (!($flow = $manager->restore())) {
      $flow = new FlowConfig();
      $flow->configure();
    }
    
    $controller->flow = $flow;
    
    if ($flow->isInFlow()) {
      if ($flow->canTransitTo($action)) {
        $guard = parent::executeAction($action);
        
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
        parent::executeAction($action);
      } else {
        $this->setActionToDestination(self::INVALID_ACTION);
        parent::executeAction(self::INVALID_ACTION);
      }
      
      $manager->save($flow);
      $this->assignToken($manager, $controller, $flow);
    } else {
      if ($flow->isEntryActivity($action)) {
        $logger->log("{$action} is entry activity");
        $flow->start($action);
        $this->assignToken($manager, $controller, $flow);
        parent::executeAction($action);
        $manager->save($flow);
      } elseif ($flow->isEndActivity($action)) {
        $manager->remove();
      } elseif (!$flow->isActivity($action)) {
        parent::executeAction($action);
      } else {
        $this->setActionToDestination(self::INVALID_ACTION);
        parent::executeAction(self::INVALID_ACTION);
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
