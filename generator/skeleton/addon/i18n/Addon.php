<?php

class I18n_Addon extends Sabel_Object
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
    $i18n = new I18n_Processor("i18n");
    $bus->getList()->find("request")->insertNext("i18n", $i18n);
  }
}
