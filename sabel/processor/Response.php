<?php

/**
 * Sabel_Processor_Response
 *
 * @category   Processor
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Response extends Sabel_Bus_Processor
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
    
    $response->outputHeader();
    
    $executerProcessor = $bus->getGroupProcessor("executer", "executer");
    
    if ($response->isNotFound() || $response->isServerError()) {
      if ($response->isNotFound()) {
        $destination->setAction("notFound");
      } elseif ($response->isServerError()) {
        $destination->setAction("serverError");
      }
      
      $response = $executerProcessor->get()->executeAction($bus);
      
      if ($response->isNotFound()) {
        $destination->setController("index");
        $controller = $creator->create($destination);
        $response->setController($controller);
        $response = $executerProcessor->executeAction($bus);
      }
    }
    
    $controller->setup($request, $destination, $storage);
    
    if (!$response->hasController()) {
      $response->setController($controller);
    }
  }
}
