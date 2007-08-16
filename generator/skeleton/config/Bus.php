<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $this->addGroup(new Sabel_Processor_Request("request"));
    $this->addGroup(new Sabel_Processor_Router("router"));
    $this->addGroup(new Sabel_Processor_Helper("helper"));
    $this->addGroup(new Sabel_Processor_Creator("creator"));
    $this->addGroup(new Sabel_Processor_Executer("executer"));
    $this->addGroup(new Sabel_Processor_Response("response"));
    
    $redirecter = new Sabel_Processor_Redirecter("redirecter");
    $this->getGroup("executer")->get("executer")->insertPrevious($redirecter);
    $this->getGroup("executer")->get("executer")->insertNext($redirecter);
                               
    $this->addGroup(new Sabel_Processor_Renderer("renderer"));
    
    return $this;
  }
}
