<?php

class Config_Factory extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Sabel_Response")->to("Sabel_Response_Web");
  }
}
