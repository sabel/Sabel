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
    $action = $this->destination->getAction();
    
    try {
      $this->controller->setAction($action);
      $this->controller->initialize();
      $this->response = $this->controller->execute($action)->getResponse();
    } catch (Exception $e) {
      l($e->getMessage());
      $this->response->serverError();
      if (ENVIRONMENT === PRODUCTION) {
        $this->destination->setAction("invalid");
      } else {
        $this->destination->setAction("serverError");
      }
      
      Sabel_Context::getContext()->setException($e);
    }
  }
}
