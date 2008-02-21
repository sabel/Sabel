<?php

/**
 * Processor_Router
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Router extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request = $bus->get("request");
    $config = $bus->getConfig("map");
    $config->configure();
    
    if ($candidate = $config->getValidCandidate($request)) {
      Sabel_Context::getContext()->setCandidate($candidate);
      $bus->set("destination", $candidate->getDestination());
      
      foreach ($candidate->getUriParameters() as $name => $value) {
        $request->setParameterValue($name, $value);
      }
    } else {
      throw new Sabel_Exception_Runtime("map not match.");
    }
  }
}
