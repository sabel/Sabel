<?php

class Form_Addon extends Sabel_Object
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
    $bus->attachExecuteEvent("initializer", $this, "eventCallback");
  }
  
  public function eventCallback($bus)
  {
    $form = new Form_Processor("form");
    $bus->get("controller")->setAttribute("form", $form);
    $bus->getList()->find("initializer")->insertNext("form", $form);
  }
}