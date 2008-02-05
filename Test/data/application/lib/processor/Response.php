<?php

class TestProcessor_Response extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response  = $bus->get("response");
    $responses = array_merge($response->getResponses(),
                             $bus->get("controller")->getAttributes());
                            
    $response->setResponses($responses);
  }
  
  public function shutdown($bus)
  {
    $bus->get("response")->outputHeader();
  }
}
