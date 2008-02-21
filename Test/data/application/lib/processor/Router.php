<?php

class TestProcessor_Router extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request = $bus->get("request");
    $config = $bus->getConfig("map");
    $config->configure();
    
    if ($candidate = $config->getValidCandidate($request->getUri())) {
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
