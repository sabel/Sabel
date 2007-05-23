<?php

class Factory extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Sabel_Request")->to("Sabel_Request_Web");
    
    $this->bind("Sabel_Controller_Executer")
          ->to("Sabel_Controller_Executer_Flow");
  }
}
