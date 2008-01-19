<?php

/**
 * Processor_Response
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Response extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $responses = array_merge($this->response->getResponses(),
                             $this->controller->getAttributes());
                            
    $this->response->setResponses($responses);
  }
  
  public function shutdown($bus)
  {
    $this->response->outputHeader();
  }
}
