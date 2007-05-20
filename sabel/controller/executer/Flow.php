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
class Sabel_Controller_Executer_Flow extends Sabel_Controller_Executer
{
  const INVALID_ACTION = "invalid";
  
  /**
   * execute an action.
   * overwrite parent executeAction method.
   *
   * @param string $action
   */
  protected function executeAction($action)
  {
    $logger = Sabel_Context::getLogger();
    
    $manager = new Sabel_Controller_Flow_Manager();
    
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
          $flow->transit($action);
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
      
      $token = $manager->getToken();
      $controller->token = $token;
      Sabel_View::assign("token", $token);
      Sabel_View::assignByArray($flow->toArray());
    } else {
      if ($flow->isEntryActivity($action)) {
        $logger->log("{$action} is entry activity");
        $flow->start($action);
        parent::executeAction($action);
        $manager->save($flow);
      } elseif ($flow->isEndActivity($action)) {
        $manager->remove();
      } else {
        $this->setActionToDestination(self::INVALID_ACTION);
        parent::executeAction(self::INVALID_ACTION);
      }
    } 
  }
}
