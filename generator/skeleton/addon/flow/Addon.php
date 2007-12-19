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
    $executer = $bus->getList()->find("executer");
    
    if (is_object($executer)) {
      $flowProcessor = new Flow_Processor("flow");
      $executer->insertPrevious("flow", $flowProcessor);
      $bus->attachExecuteAfterEvent("executer", $flowProcessor, "afterExecute");
    }
  }
}
