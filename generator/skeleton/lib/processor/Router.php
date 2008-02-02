<?php

/**
 * Processor_Router
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Router extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request = $bus->get("request");
    $validCandidate = null;
    
    $config = $bus->getConfig("map");
    $config->configure();
    
    foreach ($config->build() as $candidate) {
      if ($candidate->evaluate($request)) {
        $validCandidate = $candidate;
        break;
      }
    }
    
    if ($validCandidate === null) {
      throw new Sabel_Exception_Runtime("map not match.");
    } else {
      Sabel_Context::getContext()->setCandidate($validCandidate);
      $bus->set("destination", $validCandidate->getDestination());
      
      foreach ($validCandidate->getElements() as $element) {
        $request->setParameterValue($element->name, $element->variable);
      }
    }
  }
}
