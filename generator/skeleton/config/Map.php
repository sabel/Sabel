<?php

class Config_Map extends Sabel_Map_Configurator
{
  public function configure()
  {
    if (ENVIRONMENT !== PRODUCTION) {
      $this->route("devel")
             ->uri("devel/:controller/:action/:param")
             ->module("devel")
             ->defaults(array(":controller" => "main",
                              ":action"     => "index",
                              ":param"      => null));
    }
    
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index"));
  }
}
