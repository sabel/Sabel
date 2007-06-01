<?php

class Map extends Sabel_Map_Config
{
  public function configure()
  {
    $this->route("default")
           ->uri(":controller/:action/:id")
           ->module("index")
           ->defaults(array(":controller" => "index",
                            ":action"     => "index",
                            ":id"         => null));
  }
}
