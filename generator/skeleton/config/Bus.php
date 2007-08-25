<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    // $this->add(new Sabel_Processor_Selecter("selecter"));
    $this->add(new Sabel_Processor_Request("request"));
    $this->add(new Sabel_Processor_Router("router"));
    // $this->add(new Processor_I18n("i18n"));
    $this->add(new Sabel_Processor_Helper("helper"));
    $this->add(new Sabel_Processor_Creator("creator"));
    $this->add(new Processor_Initializer("initializer"));
    $this->add(new Sabel_Processor_Redirecter("redirecter"));
    // $this->add(new Processor_Acl("acl"));
    $this->add(new Processor_Model("model"));
    $this->add(new Sabel_Processor_Executer("executer"));
    $this->bus->addProcessorAndListener(new Processor_Errors("errors"));
    $this->add(new Sabel_Processor_Response("response"));
    $this->add(new Sabel_Processor_Renderer("renderer"));
    
    return $this;
  }
}
