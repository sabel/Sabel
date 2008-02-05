<?php

class TestProcessor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if (!$bus->has("request")) {
      $builder = new Sabel_Request_Builder();
      $request = new Sabel_Request_Object();
      $builder->build($request);
      $bus->set("request", $request);
    }
    
    if (!$bus->has("storage")) {
      $bus->set("storage", Sabel_Storage_Session::create());
    }
    
    $bus->get("storage")->start();
  }
}
