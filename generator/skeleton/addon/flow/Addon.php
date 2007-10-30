<?php

class Flow_Addon extends Sabel_Object
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
    $bus->attachExecuteEvent("creator", $this, "eventCallback");
  }
  
  public function eventCallback($bus)
  {
    $controller = $bus->get("controller");
    $reflection = $controller->getReflection();
    
    $annot = $reflection->getAnnotation("executer");
    if ($annot === null || $annot[0][0] !== "flow") return true;
    
    $flow       = new Flow_Processor("executer");
    $redirecter = new Flow_Redirecter("redirecter");
    
    $bus->getList()->find("executer")->replace($flow);
    $bus->getList()->find("redirecter")->replace($redirecter);
  }
}