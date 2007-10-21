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
    
    $this->controller->setAction($action);
    $this->controller->initialize();
    
    $this->response = $this->controller->execute($action)->getResponse();
    
    if ($this->repository->find($action) !== false) {
      $this->response->success();
    } elseif (!$this->controller->isExecuted()) {
      if ($this->response->isNotFound()) {
        $this->destination->setAction("notFound");
      } elseif ($this->response>isServerError()) {
        $this->destination->setAction("serverError");
      }
      
      $this->response = $this->controller->execute($action)->getResponse();
    }
  }
}