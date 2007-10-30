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
    return false;
  }
  
  public function loadProcessor($bus)
  {
    $type = Sabel_I18n_Gettext::SABEL;

    // $type = Sabel_I18n_Gettext::GETTEXT;
    // $type = Sabel_I18n_Gettext::PHP_GETTEXT;
    Sabel_I18n_Gettext::getInstance()->init($type);
  }
}