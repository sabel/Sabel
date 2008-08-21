<?php

class Config_Container extends Sabel_Container_Injection
{
  public function configure()
  {
    
  }
}

Sabel_Container::addConfig("default", new Config_Container());
