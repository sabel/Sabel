<?php

/**
 * Processor_Action
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Action extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $status     = $bus->get("response")->getStatus();
    $redirector = $bus->get("redirector");
    $controller = $bus->get("controller");
    
    if ($status->isFailure() || $redirector->isRedirected()) return;
    
    $action    = $bus->get("destination")->getAction();
    $hasAction = $controller->hasMethod($action);
    $request   = $bus->get("request");
    
    try {
      $controller->setAction($action);
      $controller->initialize();
      
      if ($status->isFailure() || $redirector->isRedirected()) return;
      
      if ($hasAction) {
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $status->setCode(Sabel_Response::INTERNAL_SERVER_ERROR);
      Sabel_Context::getContext()->setException($e);
    }
    
    if ($controller->getAttribute("layout") === false) {
      $bus->set("noLayout", true);
    }
  }
}
