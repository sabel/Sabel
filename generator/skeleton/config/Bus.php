<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $this->add(new Sabel_Processor_Request("request"));
    // $this->add(new Processor_I18n("i18n"));
    $this->add(new Sabel_Processor_Router("router"));
    $this->add(new Sabel_Processor_Helper("helper"));
    $this->add(new Sabel_Processor_Creator("creator"));
    $this->add(new Sabel_Processor_Redirecter("redirecter"));
    $this->add(new Processor_Model("model"));
    $this->add(new Sabel_Processor_Executer("executer"));
    $this->add(new Sabel_Processor_Response("response"));
    $this->add(new Sabel_Processor_Renderer("renderer"));
        
    // $selecter = new Sabel_Processor_Selecter("selecter");
    // $this->bus->getList()->getFirst()->insertPrevious($selecter);
    
    $errors = new Processor_Errors("errors");
    $this->bus->getList()->find("executer")->insertNext($errors);
    $this->bus->addProcessorAsListener($errors);
    
    // $acl = new Processor_Acl("acl");
    // $this->bus->getList()->find("executer")->insertPrevious($acl);
    
    return $this;
  }
}
