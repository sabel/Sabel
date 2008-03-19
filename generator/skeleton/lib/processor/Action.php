<?php

/**
 * Processor_Action
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Action extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response   = $bus->get("response");
    $controller = $bus->get("controller");
    
    if ($response->isFailure() || $controller->isRedirected()) return;
    
    try {
      $action = $bus->get("destination")->getAction();
      $controller->setAction($action);
      $controller->initialize();
      
      if (!$response->isFailure() && !$controller->isRedirected()) {
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $response->serverError();
      Sabel_Context::getContext()->setException($e);
    }
  }
}
