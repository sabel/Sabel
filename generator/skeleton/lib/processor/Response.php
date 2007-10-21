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
    $response    = $bus->get("response");
    $destination = $bus->get("destination");
    $controller  = $bus->get("controller");
    $request     = $bus->get("request");
    $storage     = $bus->get("storage");
    
    if (!is_object($response)) {
      $response = $controller->getResponse();
      $response->notFound();
    }
    
    $response->setResponses($controller->getAttributes());
  }
}
