<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $this->addAsGroup(new Sabel_Processor_Request("request"));
    // $this->addAsGroup(new Sabel_Processor_I18n("i18n"));
    $this->addAsGroup(new Sabel_Processor_Router("router"));
    $this->addAsGroup(new Sabel_Processor_Helper("helper"));
    $this->addAsGroup(new Sabel_Processor_Creator("creator"));
    $this->addAsGroup(new Sabel_Processor_Executer("executer"));
    $this->addAsGroup(new Sabel_Processor_Response("response"));
    $this->addAsGroup(new Sabel_Processor_Renderer("renderer"));
    
    $redirecter = new Sabel_Processor_Redirecter("redirecter");
    $this->get("executer")->get("executer")->insertPrevious($redirecter);
    $this->get("executer")->get("executer")->insertNext($redirecter);
    
    $selecter = new Sabel_Processor_Selecter("selecter");
    $this->get("executer")->get("executer")->getFirst()->insertPrevious($selecter);
    
    $errors = new Processor_Errors("errors");
    $this->get("executer")->get("executer")->insertNext($errors);
    
    // $acl = new Processor_Acl("acl");
    // $this->get("executer")->get("executer")->getFirst()->insertPrevious($acl);
    
    return $this;
  }
}
