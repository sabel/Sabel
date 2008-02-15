<?php

class TestProcessor_Router extends Sabel_Bus_Processor
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
