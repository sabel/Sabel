<?php

/**
 * Sabel_Processor_Executer
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Executer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $injector = Sabel_Container::injector(new Factory());
    
    $controller  = $bus->get("controller");
    $request     = $bus->get("request");
    $destination = $bus->get("destination");
    $storage     = $bus->get("storage");
    
    $controller->setAction($destination->getAction());
    $controller->initialize();
    
    $response = $controller->execute($destination->getAction());
    $bus->set("response",   $response);
    
    return true;
  }
}
