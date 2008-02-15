<?php

class TestProcessor_Session extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if (!$bus->has("session")) {
      $bus->set("session", Sabel_Session_PHP::create());
    }
  }
  
  public function shutdown($bus)
  {
    $session = $bus->get("session");
    
    if (!$session->isCookieEnabled() && !$session instanceof Sabel_Session_PHP) {
      output_add_rewrite_var($session->getName(), $session->getId());
    }
  }
}
