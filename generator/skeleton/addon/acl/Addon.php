<?php

class Acl_Addon extends Sabel_Object
{
  const VERSION = 1;
  
  public function version()
  {
    return self::VERSION;
  }
  
  public function load()
  {
    return true;
  }
  
  public function loadProcessor($bus)
  {
    $bus->attachExecuteEvent("redirecter", $this, "eventCallback");
  }
  
  public function eventCallback($bus)
  {
    $acl = new Acl_Processor("acl");
    $bus->getList()->find("redirecter")->insertNext("acl", $acl);
  }
}