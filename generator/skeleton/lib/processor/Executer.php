<?php

/**
 * Processor_Executer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Executer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if ($this->response->isFailure()) return;
    
    $action = $this->destination->getAction();
    $controller = $this->controller;
    
    try {
      $controller->setAction($action);
      $controller->initialize();
      
      if (!$this->response->isFailure()) {
        l("execute action '{$action}'");
        $controller->execute();
      }
    } catch (Exception $e) {
      $this->response->serverError();
      $this->destination->setAction("serverError");
      Sabel_Context::getContext()->setException($e);
    }
  }
}
