<?php

class Sabel_Processor_I18n extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    Sabel_I18n_Gettext::getInstance()->init();
  }
}
