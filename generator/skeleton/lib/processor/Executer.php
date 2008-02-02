<?php

/**
 * Processor_Executer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Executer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response = $bus->get("response");
    if ($response->isFailure()) return;
    
    $action = $bus->get("destination")->getAction();
    $controller = $bus->get("controller");
    
    try {
      $controller->setAction($action);
      $controller->initialize();
      
      if (!$response->isFailure()) {
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $response->serverError();
      $bus->get("destination")->setAction("serverError");
      Sabel_Context::getContext()->setException($e);
    }
  }
}
