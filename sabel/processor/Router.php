<?php

/**
 * Sabel_Processor_Router
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Router extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $router = new Sabel_Router_Map();
    
    $request = $bus->get("request");
    $destination = $router->route($request);
    
    $bus->set("router", $router);
    $bus->set("destination", $destination);
  }
}
