<?php

/**
 * Sabel_Processor_Creator
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Creator extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $injector = Sabel_Container::injector(new Factory());
    
    $request     = $bus->get("request");
    $destination = $bus->get("destination");
    $storage     = $bus->get("storage");
    
    $creator = new Sabel_Controller_Creator(); 
    $controller = $creator->create($destination);
    $controller->setBus($bus);
    
    $bus->set("controller", $controller);
  }
}
