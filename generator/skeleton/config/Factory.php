<?php

class Factory extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->bind("Sabel_Response")->to("Sabel_Response_Web");
    $this->bind("Sabel_Locale")->to("Sabel_Locale_Null");
    
    $this->bind("Sabel_Controller_Executer")
          ->to("Sabel_Controller_Executer_Basic");
  }
}
