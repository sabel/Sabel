<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $this->addAsGroup(new Sabel_Processor_Request("request"));
    $this->addAsGroup(new Sabel_Processor_Router("router"));
    $this->addAsGroup(new Sabel_Processor_Helper("helper"));
    $this->addAsGroup(new Sabel_Processor_Creator("creator"));
    $this->addAsGroup(new Sabel_Processor_Executer("executer"));
    $this->addAsGroup(new Sabel_Processor_Response("response"));
    
    $redirecter = new Sabel_Processor_Redirecter("redirecter");
    $this->get("executer")->get("executer")->insertPrevious($redirecter);
    $this->get("executer")->get("executer")->insertNext($redirecter);
                               
    $this->addAsGroup(new Sabel_Processor_Renderer("renderer"));
    
    return $this;
  }
}
