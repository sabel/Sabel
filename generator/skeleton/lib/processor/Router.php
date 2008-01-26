<?php

/**
 * Processor_Router
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Router extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request = $bus->get("request");
    $router = new Sabel_Router_Map();
    
    $bus->set("destination", $router->route($request, new Config_Map()));
    foreach ($router->getCandidate()->getElements() as $element) {
      $request->setParameterValue($element->name, $element->variable);
    }
  }
}
