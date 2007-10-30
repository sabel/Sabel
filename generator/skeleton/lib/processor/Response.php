<?php

/**
 * Processor_Response
 *
 * @category   Processor
 * @package    controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Response extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $responses  = $this->response->getResponses();
    $attributes = $this->controller->getAttributes();
    $this->response->setResponses(array_merge($responses, $attributes));
    
    if ($this->response->isNotFound()) {
      $this->destination->setAction("notFound");
    }
  }
}
