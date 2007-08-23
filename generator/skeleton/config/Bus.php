<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $this->addAsGroup(new Sabel_Processor_Request("request"));
    // $this->addAsGroup(new Processor_I18n("i18n"));
    $this->addAsGroup(new Sabel_Processor_Router("router"));
    $this->addAsGroup(new Sabel_Processor_Helper("helper"));
    $this->addAsGroup(new Sabel_Processor_Creator("creator"));
    $this->addAsGroup(new Sabel_Processor_Executer("executer"));
    $this->addAsGroup(new Sabel_Processor_Response("response"));
    $this->addAsGroup(new Sabel_Processor_Renderer("renderer"));
    
    $errors = new Processor_Errors("errors");
    $this->bus->getList()->find("executer")->insertPrevious($errors);
    $this->bus->addProcessorAsListener($errors);
    
    // $acl = new Processor_Acl("acl");
    // $this->bus->getList()->find("executer")->insertPrevious($acl);
    
    return $this;
  }
}
