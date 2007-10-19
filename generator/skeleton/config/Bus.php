<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $this->add(new Processor_Request("request"));
    $this->add(new Processor_Router("router"));
    $this->add(new Processor_Location("location"));
    // $this->add(new Processor_I18n("i18n"));
    $this->add(new Processor_Helper("helper"));
    $this->add(new Processor_Creator("creator"));
    // $this->add(new Processor_Selecter("selecter"));
    $this->add(new Processor_Initializer("initializer"));
    $this->add(new Processor_Redirecter("redirecter"));
    $this->add(new Processor_Form("form"));
    // $this->add(new Processor_Acl("acl"));
    $this->add(new Processor_Executer("executer"));
    $this->add(new Processor_Response("response"));
    $this->add(new Processor_Renderer("renderer"));
    
    return $this;
  }
}
