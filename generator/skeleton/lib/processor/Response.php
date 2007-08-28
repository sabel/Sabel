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
    
    $creator = new Sabel_Controller_Creator();
    
    $response->setDestination($destination);
        
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
    
    $controller->setup($request, $destination, $storage);
    
    if (!$response->hasController()) {
      $response->setController($controller);
    }
  }
}
