<?php

class TestProcessor_Router extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request = $bus->get("request");
    $config = $bus->getConfig("map");
    $config->configure();
    
    if ($candidate = $config->getValidCandidate($request->getUri())) {
      $request->setParameterValues($candidate->getUriParameters());
      $bus->set("destination", $candidate->getDestination());
      Sabel_Context::getContext()->setCandidate($candidate);
    } else {
      $message = __METHOD__ . "() didn't match to any routing configuration.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
}
