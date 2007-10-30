<?php

class Helper_Addon extends Sabel_Object
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
    $bus->attachExecuteBeforeEvent("initializer", $this, "eventCallback");
  }
  
  public function eventCallback($bus)
  {
    $helper = new Helper_Processor("helper");
    $bus->getList()->find("initializer")->insertPrevious($helper);
  }
}