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
   * overwrite parent method.
   *
   * @param string $action
   */
  protected function executeAction($action)
  {
    $controller = $this->getController();
    
    $storage = $controller->getStorage();
    $action  = $controller->getAction();
    
    if (!($flow = $storage->read("flow"))) {
      $flow = new FlowConfig();
      $flow->configure();
    }
    
    $GLOBALS["flow"] = $flow;
    
    if ($flow->isInFlow()) {
      if ($flow->canTransitTo($action)) {
        $guard = $controller->execute($action);
        if ($guard === null) $guard = true;
        
        if ($guard) {
          $flow->transit($action);
        } else {
          $controller->redirectTo($flow->getCurrentActivity()->getName());
        }
      } elseif ($flow->isCurrent($action)) {
        $controller->execute($action);
      } else {
        $this->setActionToDestination(self::INVALID_ACTION);
        $controller->execute(self::INVALID_ACTION);
      }
      $storage->write("flow", $flow);
    } else {
      if ($flow->isEntryActivity($action)) {
        $flow->start($action);
        $storage->write("flow", $flow);
      } elseif ($flow->isEndActivity($action)) {
        $storage->delete("flow");
      } else {
        $this->setActionToDestination(self::INVALID_ACTION);
        $controller->execute(self::INVALID_ACTION);
      }
    } 
  }
}
