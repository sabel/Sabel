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
    
    $responses  = $response->getResponses();
    $attributes = $controller->getAttributes();
    $response->setResponses(array_merge($responses, $attributes));
    
    $creator = new Sabel_Controller_Creator();
        
    if ($response->isNotFound() || $response->isServerError()) {
      if ($response->isNotFound()) {
        $destination->setAction("notFound");
      } elseif ($response->isServerError()) {
        $destination->setAction("serverError");
      }
      
      $response = $controller->execute($destination->getAction());
      
      if ($response->isNotFound()) {
        $destination->setController("index");
        $controller = $creator->create($destination);
        $response = $controller->execute($destination->getAction());
      }
    }
  }
}
