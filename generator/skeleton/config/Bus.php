<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $this->add(new Processor_Addon("addon"));
    $this->add(new Processor_Request("request"));
    $this->add(new Processor_Router("router"));
    $this->add(new Processor_Controller("controller"));
    $this->add(new Processor_Location("location"));
    $this->add(new Processor_Initializer("initializer"));
    $this->add(new Processor_Redirector("redirector"));
    $this->add(new Processor_Executer("executer"));
    $this->add(new Processor_Exception("exception"));
    $this->add(new Processor_Response("response"));
    $this->add(new Processor_View("view"));
    
    return $this;
  }
}
