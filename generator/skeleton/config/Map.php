<?php

class Config_Map extends Sabel_Map_Configurator
{
  public function configure()
  {
    if (ENVIRONMENT === DEVELOPMENT) {
      $this->route("admin")
             ->uri("admin/:controller/:action/:param")
             ->module("admin")
             ->defaults(array(":controller" => "config",
                              ":action"     => "file",
                              ":param"      => null));
    }
    
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index"));
  }
}
