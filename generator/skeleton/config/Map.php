<?php

class Map extends Sabel_Map_Config
{
  public function configure()
  {
    $this->route("admin")
           ->uri("admin/:controller/:action")
           ->module("admin")
           ->defaults(array(":controller" => "config",
                            ":action"     => "file"));
                            
    $this->route("default")
           ->uri(":controller/:action")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index"));
  }
}
